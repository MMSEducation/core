<?php

namespace Chatter\Core\Http\Controllers\Api;

use Chatter\Core\Helpers\ChatterHelper;
use DB;
use Auth;
use Illuminate\Http\Request;
use Chatter\Core\Models\Post;
use Chatter\Core\Models\Category;
use Illuminate\Routing\Controller;
use Chatter\Core\Models\Discussion;
use Chatter\Core\Models\PostInterface;
use Chatter\Core\Events\DiscussionEvents;
use Chatter\Core\Models\CategoryInterface;
use Chatter\Core\Models\DiscussionResource;
use Chatter\Core\Models\DiscussionInterface;
use Chatter\Core\Models\DiscussionCollection;
use Chatter\Core\Events\AfterCreateDiscussion;
use Chatter\Core\Events\BeforeCreateDiscussion;
use Chatter\Core\Http\Requests\StoreDiscussionRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DiscussionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')
            ->except(['index', 'show'])
        ;
    }

    /**
     * Display discussions
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($request->has('category')) {
            $category = app(CategoryInterface::class)::where('slug', $request->category)->first();

            $collection = new DiscussionCollection($category->discussions()
                ->orderBy('updated_at', 'asc')
                ->paginate(config('chatter.paginate.discussions')));

            $collection->category = $category;
            
            return $collection;
        }

        return new DiscussionCollection(app(DiscussionInterface::class)::paginate(config('chatter.paginate.discussions')));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDiscussionRequest $request)
    {
        $user = Auth::user();
        $discussionModel = app(DiscussionInterface::class);
        $discussion = new $discussionModel();
        $discussion->fill($request->all());

        event(DiscussionEvents::PRE_CREATE, new BeforeCreateDiscussion($discussion));

        $discussion->user_id = $user->id;
        $discussion->save();
        $discussion->users()->save($user);
        $postModel = app(PostInterface::class);
        $post = new $postModel();
        $post->fill($request->all());
        $post->user_id = $user->id;
        $discussion->posts()->save($post);

        event(DiscussionEvents::POST_CREATE, new AfterCreateDiscussion($discussion));

        return new DiscussionResource($discussion);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $discussion = app(DiscussionInterface::class)::where('id', ChatterHelper::toQueryableId($id))
            ->orWhere('slug', $id)
            ->firstOrFail();

        $discussion->increment('views');

        return new DiscussionResource($discussion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $discussion = app(DiscussionInterface::class)::findOrFail($id);
        $permission = ChatterHelper::checkPermission(Auth::user(),$discussion->posts()->orderBy('id')->first()->id);
        if ($discussion->user->id !== Auth::user()->id && !$permission['canEdit']) {
            abort(403, 'Unauthorized action.');
        }
        
        $discussion->fill($request->all());
        $discussion->save();

        return new DiscussionResource($discussion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $discussion = app(DiscussionInterface::class)::findOrFail($id);
        $permission = ChatterHelper::checkPermission(Auth::user(),$discussion->posts()->orderBy('id')->first()->id);
        if ($discussion->user->id !== Auth::user()->id && !$permission['canDelete']) {
            abort(403, 'Unauthorized action.');
        }
        return response()->json([
            'deleted' => $discussion->delete()
        ]);
    }
}
