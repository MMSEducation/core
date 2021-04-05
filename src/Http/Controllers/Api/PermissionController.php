<?php 
namespace Chatter\Core\Http\Controllers\Api;
use \Illuminate\Http\Request;
use Auth;
use Chatter\Core\Helpers\ChatterHelper;
use Illuminate\Routing\Controller;
class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index(Request $request,$id){
        $user = Auth::user();
        return ChatterHelper::checkPermission($user,$id);
    }
}
?>