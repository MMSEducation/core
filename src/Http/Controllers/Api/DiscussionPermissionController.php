<?php 
use \Illuminate\Http\Request;

use Chatter\Core\Helpers\ChatterHelper;

class DiscussionPermissionController extends Controller
{
    
    public function index(Request $request,$id){
        $user = Auth::user();
        return ChatterHelper::checkPermission($user,$id);
    }
}
?>