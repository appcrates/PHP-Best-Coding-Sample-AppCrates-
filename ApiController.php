<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Common;
use Auth;
//use Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

require  $_SERVER['DOCUMENT_ROOT'].'/join_app/twilio-php-master/Twilio/autoload.php';
use Twilio\Rest\Client;
class ApiController extends Controller
{
private $limit = '8';
  public function new_user()
	{		
		$input = Input::all();
        $f_name = Input::get('f_name');
		$l_name = Input::get('l_name');
		$email = Input::get('email');
		$phone = Input::get('phone');
		$user_name = Input::get('user_name');
      $country_code = Input::get('country_code'); 
		$password = Input::get('password');
		$where="email='".$email."' OR phone='".$phone."' OR user_name='".$user_name."'";
		$select=array('id');
		//echo $user_name;exit;
		$check_user=Common::search_data_single($select,$where,'users');   
		if($check_user)
		{
		  return response()->json(['code' => 100,'data'=>'Email / Phone / Username already exits'],200);
          die;  
		}
		else
		{
			$data = array('email' => $email,
						  'password' => md5($password), 
						  'f_name' => $f_name,
						  'phone'=>$phone,
						  'created_at'=>Carbon::now(),
						  'updated_at'=>Carbon::now(),
						  'l_name'=>$l_name,
              'country_code'=>$country_code,
						  'user_name'=>$user_name,
              'full_number'=>$country_code.$phone
						);

			$result_id = Common::insert_data($data, "users");      
       		$login_token= Common::random_string($result_id);	
       		$data = array('email' => $email, 
                    'f_name' => $f_name,
                    'phone'=>$phone,
                    'l_name'=>$l_name,
                    'user_id'=>$result_id,
                    'picture'=>null,
                    'cover_img'=>null,
                    'user_name'=>$user_name,
                  "user_token"=>$login_token,);
            return response()->json(['code' => 200,'data'=>$data],200);
            die;
		}
	}
	public function login(){
 		
		$input = Input::all();        
		$email = Input::get('email');	
		$password = Input::get('password');
		$where="(email='".$email."' OR phone='".$email."' OR user_name='".$email."') AND password ='".md5($password)."'";
		$select=array('*');
		$check_user=Common::search_data_single($select,$where,'users');   
		if($check_user)
		{
			 $login_token= Common::random_string($check_user->id);
			  $user_data=array(
                "user_id"=>$check_user->id,
                "f_name"=>$check_user->f_name,
                "l_name"=> $check_user->l_name,
                "email"=>$check_user->email,
                "user_name"=>$check_user->user_name,
                "user_token"=>$login_token,
                "picture"=>$check_user->picture,
                'cover_img'=>$check_user->cover_img,
              );
               return response()->json(['code' => 200, 'data' => $user_data], 200);
		}
		else
		{
			 return response()->json(['code' => 100, 'message' => 'Invalid Username or Password.'], 400);
		}
		die;
    }
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/
/******************************************************************************************************/


   public function social_signup()
    {
      
        $input = Input::all();
        $f_name = Input::get('f_name');
        $l_name = Input::get('l_name');
        $email = Input::get('email');
        $s_n_name = Input::get('s_n_name');
        $user_token = Input::get('user_token');
        $image = Input::get('image'); 

      	$where="email='".$email."'";
		$select=array('*');
		$check_user=Common::search_data_single($select,$where,'users'); 
        	if($check_user){   
            $login_token= Common::random_string($check_user->id);
            $data = array('email' => $email, 
                          'f_name' => $f_name,                
                          'l_name'=>$l_name,
                          'user_id'=>$check_user->id,
                          'picture'=>$image,
                          'cover_img'=>$check_user->cover_img,
                          'user_name'=>$check_user->user_name,
                          'user_token'=>$login_token
                        );

           return response()->json(['code' => 200,'data'=>$data],200);
        	}
        	else
        	{
        		 $data = array(
                    'email' => $email,
                    'f_name' => $f_name,
                    'l_name'=>$l_name,
                    's_n_name'=>$s_n_name,
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),                    
                    'picture'=>$image,
                    's_n_name'=>$s_n_name,
                    'user_token'=>$user_token
            	);
            	$result_id = Common::insert_data($data, "users"); 
            	$login_token= Common::random_string($result_id);
            	$data = array('email' => $email, 
                          'f_name' => $f_name,                
                          'l_name'=>$l_name,
                          'user_id'=>$result_id,
                          'user_name'=>null,
                          'picture'=>$image,
                          'cover_img'=>null,                          
                          'user_token'=>$login_token
                        );
            	 return response()->json(['code' => 200,'data'=>$data],200);
        	}
       
    }


	



    public function change_password()
    {
       $input = Input::all();
        $old_pass='123456';
        $new_pass='56454984';
        $user_id='4';
        $where = array("password" => Hash::check($old_pass),"id"=>$user_id);
        $result=Common::data_by_with($where,"users");
        print_r($result);
    }



/**************************************************************************************************/



public function get_profile(Request $request)
    {
      $input = Input::all();
      $user_id=Input::get('user_id');
      $user_token=Input::get('user_token');
        $where = array("id" => $user_id,"login_token" => $user_token);
        $token=Common::data_by_with($where,"users");
      if($token)
      {
        if(Input::get('member_id'))
          {
            $user_id=Input::get('member_id');
          }
          $result_id = Common::single_data($user_id, "users"); 
          if($result_id)
          {
              return response()->json(['code' => 200, 'data'=>$result_id], 200);
          }
          else
          {
            return response()->json(['code' => 100, 'data'=>"No data found"], 200);
          }
       } 
       else
       {
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }
    }



public function update_profile(Request $request)
    {
     // echo $dir = $_SERVER['SERVER_NAME'];
   			
   		$input = Input::all();
    	$f_name = Input::get('f_name');
    	$l_name = Input::get('l_name');
      	$phone = Input::get('phone');
      	$address = Input::get('address');
      	$country = Input::get('country');		
      	$gender = Input::get('gender');
      	$dob  = Input::get('dob');
        $country_code=Input::get('country_code');
       
      	$cover_img=Input::get('cover_img');

      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
          if($token)
          {
      				
      				$data = array(	 				 
	    			 	'updated_at'=>Carbon::now(),
	    				'f_name'=>$f_name,
	    				'l_name'=>$l_name,
	    				'phone'=>$phone,
	    				'address'=>$address,
	    				'country'=>$country,
	    				'gender'=>$gender,
	    				'dob'=>$dob,
              'country_code'=>$country_code,
               'full_number'=>$country_code.$phone
	    				);
          	if($request->file('cover_img'))
          	{
          		$cover_img = $request->file('cover_img');
     			$cover_imge_name = time().'.'.$cover_img->getClientOriginalExtension();
      			$destinationPath1 = public_path('/cover_images');
        		$cover_img->move($destinationPath1, $cover_imge_name);
        		 $data += [ "cover_img" => url('/')."/public/cover_images/".$cover_imge_name ];
          	}
        	if($request->file('image'))
        	{
    			$image = $request->file('image');
     			$imge_name = time().'.'.$image->getClientOriginalExtension();
      			$destinationPath = public_path('/images');
        		$image->move($destinationPath, $imge_name);
        		 $data += [ "picture" => url('/')."/public/images/".$imge_name ];
        	}
        		

    			$result_id = Common::update_data($user_id,$data, "users"); 
          $user_data=Common::single_data($user_id,'users');
                return response()->json(['code' => 200,'data'=>$user_data], 200);
            

        	}
		    else
		   {
		    return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
		   }




    }



    public function search_user()
    {
      $input = Input::all();     
      $user_id=Input::get('user_id');
      $start_from=Input::get('start_from');
      $user_token = Input::get('user_token');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
           $data=array();
            $key_word=Input::get('key_word');           
            $where="status='1' AND id!=".$user_id." AND (f_name like '%".$key_word."%' OR l_name like '%".$key_word."%' OR email like '%".$key_word."%')";
            $select=array('id','f_name','l_name','picture','cover_img');
            $record = Common::search_data_with_pagination($select,$where, "users",$start_from,$this->limit);
            if($record)
             {            
                for($p=0;$p<count($record);$p++)
                {
                  $friend_bit=0;  
                   $select=array("id");
                   $where="(user_id=".$user_id." AND friend_id=".$record[$p]->id.") OR (user_id=".$record[$p]->id." AND friend_id=".$user_id.") AND status=1";
                   $friend=Common::search_single_data($select,$where,"friends");
                   if($friend)
                   {
                     $friend_bit=1;
                   }
                   else
                   {
                      $where="(request_sender=".$user_id." AND request_receiver=".$record[$p]->id.") OR (request_sender=".$record[$p]->id." AND request_receiver=".$user_id.") AND status=1";
                      $friend=Common::search_single_data($select,$where,"friends_request");
                      if($friend)
                      {
                        $friend_bit=2;
                      }
                   }
                   $cur_record=array(
                    "id"=>$record[$p]->id,
                    "f_name"=>$record[$p]->f_name,
                    "l_name"=>$record[$p]->l_name,
                    "picture"=>$record[$p]->picture,
                    "cover_img"=>$record[$p]->cover_img,
                    "friend_status"=>$friend_bit
                   );
                $data[]=$cur_record  ;                  
                   
                }    
                
                return response()->json(['code' => 200, 'data' => $data], 200);
             }
          }

        else
          {
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
          }  
             
    }

     public function deactive_user()
    {
      $input = Input::all();     
       $user_id=Input::get('user_id');
       $user_token = Input::get('user_token');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
            $where=array("status"=>'0',
            	"phone"=>'',
            	"email"=>'',
            	"user_name"=>''
        	);
            $record = Common::update_data($user_id,$where,"users");            
            if($record)
             {
                return response()->json(['code' => 200, 'data' => "User have been deleted"], 200);
             }
          }

        else
          {
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
          }  
             
    }

public function send_friend_request(Request $request){
  $input = Input::all();
            
    
   $user_token = Input::get('user_token');
    $user_id=Input::get('sender');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
          if($token)
          {


      $sender=Input::get('sender');
      $receiver=Input::get('receiver');
       
          $where = array("request_sender" => $sender,"request_receiver" => $receiver);
          $record = Common::data_by_with($where, "friends_request");
          if($record)
          {
             return response()->json(['code' => 100, 'message' => 'You have already sent the friend request to him/her'], 200);
          }
          else
          {

            $select=array("id");
            $where=array("request_sender" => $receiver,"request_receiver" => $sender);
            $search_request=Common::data_by_with($where,"friends_request");
            if($search_request)
            {
               return response()->json(['code' => 100, 'data'=>"Request already in process"], 200);
               die;
            }


             $data = array(
            'request_sender' => $sender, 
            'request_receiver' => $receiver,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            );
             $result_id = Common::insert_data($data, "friends_request");   
             /****************************************************************************/
              //                                Notification
             $sender_record=Common::single_data($sender,"users");

              $notification=$sender_record->f_name." ".$sender_record->l_name." has send you friend request";
                $data=array(
                  "sender"=>$sender,
                  "receiver"=>$receiver,
                  "notification"=>$notification,
                  "method"=>"1",
                  "request_id"=>$result_id,
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");
             /****************************************************************************/ 
            return response()->json(['code' => 200, 'message' => 'Request has been sent.','record'=>$result_id], 200);

          }           
    
        }
        else
        {
          return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }
      
}


  
public function get_friend_requests(Request $request){

   

      $input = Input::all();
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $start_from=Input::get('start_from');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
          // $where="friends_requeststatus='1' AND id!=".$user_id." AND (f_name like '%".$key_word."%' OR l_name like '%".$key_word."%' OR email like '%".$key_word."%')";
            $where = "(friends_request.request_receiver=".$user_id." AND friends_request.status='1') OR (friends_request.request_sender=".$user_id." AND friends_request.status='0')";
                $record = Common::get_friend_requests($where,$start_from,$this->limit);

                //print_r($record);exit;

                if($record)
                {
                   return response()->json(['code' => 200, 'data' => $record], 200);
                }
         }  
       else
        {
          return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }
}


public function get_notifications()
  {
     $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $stat_from=Input::get('starting_record');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){         
          $notifications=Common::get_notifications($user_id,$stat_from,$this->limit);
            return response()->json(['code' => 200, 'data'=>$notifications], 200);
       }
       else{
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
  }

 public function delete_request()
    {
      $input = Input::all();

      $user_token = Input::get('user_token');
      $user_id = Input::get('user_id');
      $notification_id = Input::get('notification_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
            $request_id = Input::get('request_id'); 
            $where = array("id" => $request_id);
            $result_id = Common::delete_record($where, "friends_request"); 
            $where = array("id" => $notification_id);
            $result_id = Common::delete_record($where, "notifications"); 
            return response()->json(['code' => 200, 'data' => 'Request has been deleted.'], 200);
         } 
        else
        {
          return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }
    }


    public function get_friends_list()
    {

      $input = Input::all();
      $user_id=Input::get('user_id');
      $user_token = Input::get('user_token');
      $start_from=Input::get('start_from');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
           
            $where = array("friends.user_id" => $user_id);
            $record = Common::friends_list($where,$start_from,$this->limit);
            if($record)
             {
                return response()->json(['code' => 200, 'data' => $record], 200);
             }
          }

        else
          {
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
          }  
             
    }


   public function add_msg()
    {
      $user_id = Input::get('user_id'); 
      $user_token = Input::get('user_token');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
          $input = Input::all();
          $receiver_id = Input::get('receiver_id');
          $msg = Input::get('message');
           /**************************************************************************/
          $where="(user=".$user_id." AND   friend=".$receiver_id.") OR user=".$receiver_id." AND   friend=".$user_id." ";
          $select=array('id');
          $cnv_id=Common::search_single_data($select,$where,"conversation");
          if($cnv_id)
          {
            $con_id=$cnv_id->id;
            $data=array(
              "updated_at"=>Carbon::now(),
              "last_msg"=>substr($msg,0,95)
            );
            Common::update_data($con_id,$data,"conversation");
          }
          else
          {
             $data_array=array(
              "user"=>$user_id,
              "friend"=>$receiver_id,
              "created_at"=>Carbon::now(),
              "updated_at"=>Carbon::now(),
              "last_msg"=>substr($msg,0,50)
             );
             $con_id=Common::insert_data($data_array,"conversation");
          }

          /**************************************************************************/
           $data = array('receiver_id' => $receiver_id, 'sender_id' => $user_id, 'message' => $msg,'created_at'=>Carbon::now(),'updated_at'=>Carbon::now(),'conversation_id'=>$con_id);
           $result_id = Common::insert_data($data,"messages");  
          //  $data = array('receiver_id' => $user_id, 'sender_id' => $receiver_id, 'message' => $msg,'created_at'=>Carbon::now(),'updated_at'=>Carbon::now(),'conversation_id'=>$con_id);
          // $result_id = Common::insert_data($data,"messages");
           /****************************************************************************/
              //                               Notification
                             
             // $notificationto=Common::single_data($user_id,"users");
             //  $notification=$notificationto->f_name." ".$notificationto->l_name." has accepted your friend request";
             //    $data=array(
             //      "sender"=>$user_id,
             //      "receiver"=>$receiver_id,
             //      "notification"=>$notification,
             //      "method"=>"3",
             //      "status"=>"1",
             //      "request_id"=>$con_id,
             //      "type"=>'0',
             //       "created_at"=>Carbon::now(),
             //      "updated_at"=>Carbon::now()
             //    );
             //    Common::insert_data($data,"notifications");
                
             /****************************************************************************/ 
          return response()->json(['code' => 200, 'data' => 'Messages sent successfully.'], 200);
          }
        else
          {
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
          }  
    }

public function msgs_with_user()
    {
      $input = Input::all();     
       $user_token = Input::get('user_token');
       $user_id=Input::get('user_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
            $friend_id=Input::get('friend_id');
            $record = Common::single_data($friend_id, "users");
            if($record)
             {
                $friend_data=array(
                  "id"=>$record->id,
                  "f_name"=>$record->f_name,
                  "l_name"=>$record->l_name,
                  "picture"=>$record->picture,
                );

                  $where="status='1' AND (receiver_id=".$user_id." AND sender_id=".$friend_id." ) OR (receiver_id=".$friend_id." AND sender_id=".$user_id." )";
                  $select=array('message','sender_id','id','created_at');
                  $record = Common::search_data($select,$where, "messages");
                  $data=array(
                    "friend_info"=>$friend_data,
                    "messages"=>$record
                  );
                return response()->json(['code' => 200, 'data' => $data], 200);
             }
          }

        else
          {
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
          }  
             
    }




    public function user_conversations()
    {
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $start_from=Input::get('start_from');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {          
             //$where="user=".$user_id." or friend=".$user_id."";
             //$select=array("id");
         	$array=array();
             $results=Common::get_conversiation($user_id,$start_from,$this->limit);
             for($i=0;$i<count($results);$i++)
             {
             	$cnv_id=$results[$i]->cnv_id;
             	$counter=Common::get_conversiationcount($user_id,$cnv_id);
             	$array[]=array(
             		'conv_details'=>$results[$i],
             		'counter'=>$counter->messagecount
             	);

             }

            // echo $results->cnv_id;
             return response()->json(['code' => 200, 'data' => $array], 200);
         } 
        else
         {
            return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
         }               
    }


public function notification_counter(){

 	 $input = Input::all();     
     $user_token = Input::get('user_token');
     $user_id=Input::get('user_id');   
     $lat=Input::get('lat');
     $long=Input::get('long');      
     $where = array("id" => $user_id,"login_token" => $user_token);
     $token=Common::data_by_with($where,"users");
    	if($token){         
          
           $messages_counter=Common::get_messagescount($user_id);
           $notifications=Common::get_notificationscount($user_id);
           $notification=array(
            'notification_counter'=>$notifications->notificationcount,
            'messages_counter'=>$messages_counter->messagecount
           );

/***********************************************************************************/
       $data=Common::auto_msg($lat,$long,$user_id); 
       //echo count($data); 
       //print_r($data);
       for($count=0;$count<count($data);$count++)
       {
        $event_id=$data[$count]->event_id; 
        $receiver_id=$data[$count]->user_id; 
        $where="user_id='".$user_id."' AND  event_id=".$event_id." AND receiver_id=".$receiver_id."";
        $select=array('id');
        $contact_detail=Common::search_data_single($select,$where,'check_notification');
        if(!$contact_detail)
        {
           $insert=array(
                  'user_id'=>$user_id,
                  'event_id'=>$event_id,
                  'created_at'=>Carbon::now(),
                  'receiver_id'=>$receiver_id
                );
                Common::insert_data($insert,'check_notification');
               $sender_record=Common::single_data($user_id,"users");
                  $notification_text=$sender_record->f_name." ".$sender_record->l_name." is also in your event";
                  $not_data=array(
                  "sender"=>$user_id,
                  "receiver"=>$receiver_id,
                  "notification"=>$notification_text,
                  "method"=>"2",
                  "request_id"=>$event_id,
                  "status"=>"1",
                  "type"=>'0',
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($not_data,"notifications");
        }
       
       }
       
  /***********************************************************************************/
             $data=Common::auto_msg2($lat,$long,$user_id);                         
             for($ii=0;$ii<count($data);$ii++){
              $event_id=$data[$ii]->event_id;                
               $where="user_id='".$user_id."' AND  event_id=".$event_id."";
                $select=array('id');
                $contact_detail=Common::search_data_single($select,$where,'user_on_event');
                if(!$contact_detail){
                  $in_event=array(
                    "event_id"=>$event_id,
                    "user_id"=>$user_id                  
                  );
                  Common::insert_data($in_event,"user_on_event");
                }
                $friends=array();
                unset($friends);
                $friends=Common::get_friends($user_id,$event_id); 
                
                for($f=0;$f<count($friends);$f++)
                {
                  $receiver_id=$friends[$f]->user_id;
                  $where="user_id='".$user_id."' AND  event_id=".$event_id." AND receiver_id=".$receiver_id."";
                  $select=array('id');
                  $contact_detail=Common::search_data_single($select,$where,'check_notification');
                  if(!$contact_detail)
                    {
                      
                       $insert=array(
                              'user_id'=>$user_id,
                              'event_id'=>$event_id,
                              'created_at'=>Carbon::now(),
                              'receiver_id'=>$receiver_id
                            );
                            Common::insert_data($insert,'check_notification');
                           $sender_record=Common::single_data($user_id,"users");
                              $notification_text=$sender_record->f_name." ".$sender_record->l_name." is also in this event";
                              $not_data=array(
                              "sender"=>$user_id,
                              "receiver"=>$receiver_id,
                              "notification"=>$notification_text,
                              "method"=>"2",
                              "request_id"=>$event_id,
                              "status"=>"1",
                              "type"=>'0',
                              "created_at"=>Carbon::now(),
                              "updated_at"=>Carbon::now()
                            );
                            Common::insert_data($not_data,"notifications");
                    }

                }


             }
           return response()->json(['code' => 200, 'data'=>$notification], 200);
        }
        else{
              return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }  

}



public function update_notification_counter(){

 	 $input = Input::all();     
     $user_token = Input::get('user_token');
     $user_id=Input::get('user_id');
  
     $where = array("id" => $user_id,"login_token" => $user_token);
     $token=Common::data_by_with($where,"users");
    	if($token){        
    		$data=array(
    			'read_bit'=>1
    		);

        $where="read_bit=0 AND receiver=".$user_id."";

           $notifications=Common::update_counter($data,'notifications',$where);
           //echo $notifications->count;
           return response()->json(['code' => 200, 'data'=>"Notification count has been updated"], 200);
        }
        else{
              return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }  

}

  
  public function add_event()
  {
    // echo date("Y-m-d h:i:s")."\n";
    // echo gmdate("Y-m-d h:i:s");
    // exit;
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){
          $type=Input::get('type');
          $title=Input::get('title');
          $event_datetime=Input::get('event_datetime');
          $lat=Input::get('lat');
          $lng=Input::get('lng');
          $venue=Input::get('venue');
          
          if(Input::get('contacts'))
            {
              $contacts=Input::get('contacts');
               $total_contacts=count($contacts);
            }
            else
            {
              $contacts=array();
              $total_contacts=0;
            }
         // $venue=Input::get('end_time_date');

          $utc_end_datetime=Input::get('utc_end_datetime');
          $utc_start_datetime=Input::get('utc_start_datetime');
          
          if(Input::get('friends')){
              $friends=Input::get('friends');
          }
          else{
            $friends=array();
          }
          $event_privacy=Input::get('event_privacy');
          $data = array(
          'type' => $type, 
          'title' => $title,
          'event_datetime'=>$event_datetime,
          'lat'=>$lat,
          'lng'=>$lng,
          'venue'=>$venue,
          'user_id'=>$user_id,
          'created_at'=>Carbon::now(),
          'updated_at'=>Carbon::now(),
          'event_privacy'=>$event_privacy,
          'utc_end_datetime'=>$utc_end_datetime,
          'utc_start_datetime'=>$utc_start_datetime     
          );
             $result_id = Common::insert_data($data, "events");   
              $total_friends=count($friends);
              $req_id="";
             for($i=0;$i<$total_friends;$i++){
                   $data = array(
                    'event_id' => $result_id, 
                    'sender' => $user_id,
                    'receiver'=>$friends[$i],                 
                    'created_at'=>Carbon::now(),
                     'updated_at'=>Carbon::now(),         
                    );
                  $req_id=Common::insert_data($data, "event_requests");                 
                /****************************************************************************/
                //                                Notification
                 $sender_record=Common::single_data($user_id,"users");
                 $notification=$sender_record->f_name." ".$sender_record->l_name." has send you event request";
                  $data=array(
                  "sender"=>$user_id,
                  "receiver"=>$friends[$i],
                  "notification"=>$notification,
                  "method"=>"2",
                  "request_id"=>$req_id,
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                  );
                  Common::insert_data($data,"notifications");
             /****************************************************************************/
              } 


              /*--------------------------------------------------------------------------*/

$account_sid = 'ACc57956848d445ef475ba019259bb79f9';
$auth_token = '24968226eb2aeab4bde66f1ee3f26a23';
$twilio_number = "+13362816746";
$client = new Client($account_sid, $auth_token);
            for($j=0;$j<$total_contacts;$j++){                
                // $where="phone='".$contacts[$j]."' AND status='1'";
                $where="full_number='".$contacts[$j]."' AND status='1'";
                 $select=array('id');
                 $contact_detail=Common::search_data_single($select,$where,'users');
            if($contact_detail){                  
                   $data = array(
                    'event_id' => $result_id, 
                    'sender' => $user_id,
                    'receiver'=>$contact_detail->id,                 
                    'created_at'=>Carbon::now(),
                     'updated_at'=>Carbon::now(),         
                    );
                  $req_id=Common::insert_data($data, "event_requests");                 
                /****************************************************************************/
                //                                Notification
                 $sender_record=Common::single_data($user_id,"users");
                 $notification=$sender_record->f_name." ".$sender_record->l_name." has send you event request";
                  $data=array(
                  "sender"=>$user_id,
                  "receiver"=>$contact_detail->id,
                  "notification"=>$notification,
                  "method"=>"2",
                  "request_id"=>$req_id,
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                  );
                  Common::insert_data($data,"notifications");
             /****************************************************************************/
             }
             else
             {
              //Send SMS on  $contacts[$j]
              $client->messages->create(  
              $contacts[$j],
                  array(
                      'from' => $twilio_number,
                      'body' => 'Hi Sir this is umar'
                  )
              );

             }
            } 

              /*-------------------------------------------------------------------------*/
           
          return response()->json(['code' => 200, 'data'=>"Event have been created"], 200);
       }
       else{
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }  
  }


  public function delete_event()
  {
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){

          $event_id=Input::get('event_id');
          $data = array(
          'status' => '0',           
          'updated_at'=>Carbon::now(),         
           );
             $result_id = Common::update_data($event_id,$data, "events");   
             return response()->json(['code' => 200,'data'=>"Event Have been deleted"], 200);

       }
       else{
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }  
  }

  public function accept_event_request()
  {
      
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){         
         
          $request_id=Input::get('request_id');
          $notification_id=Input::get('notification_id');
          
          $event_request_details=Common::single_data($request_id,"event_requests",0);
          //print_r($event_request_details);exit;
          $data=array(
            "event_id"=>$event_request_details->event_id,
            "participants_id"=>$event_request_details->receiver,
            "created_at"=>Carbon::now(),
            "updated_at"=>Carbon::now()
          );
          Common::insert_data($data,"event_participants");
          $where=array('id'=>$event_request_details->event_id);
          Common::increase($where,"total_joins","events");
          $data=array(
            'status'=>1
          );
          Common::update_data($request_id,$data,"event_requests");  

           /****************************************************************************/
              //                                Notification
              $sender_record=Common::single_data($event_request_details->receiver,"users");
              $notification=$sender_record->f_name." ".$sender_record->l_name." has accepted your event request";
                $data=array(
                  "sender"=>$event_request_details->receiver,
                  "receiver"=>$event_request_details->sender,
                  "notification"=>$notification,
                  "method"=>"2",
                  "request_id"=>"0",
                  "status"=>"1",                 
                  "type"=>'0',
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");
                

                 $data=array(
              "updated_at"=>Carbon::now()
             );
             Common::update_data($event_request_details->event_id,$data,"events");     

            $data=array(
              'status'=>'0'
             );


             Common::update_data($notification_id,$data,"notifications");            
             /****************************************************************************/ 
             return response()->json(['code' => 200, 'data'=>"Request has been accepted"], 200);
        }
       else{
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }  
       
  }


 public function un_friend()
    {
      $input = Input::all();
      $user_token = Input::get('user_token');
        $user_id = Input::get('user_id'); 
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
          
          $friend_id = Input::get('friend_id');        
          $where = array("user_id" => $user_id,"friend_id"=>$friend_id);       
          $result_id = Common::delete_record($where, "friends"); 
          $where = array("user_id" => $friend_id,"friend_id"=>$user_id);       
          $result_id = Common::delete_record($where, "friends"); 
          return response()->json(['code' => 200, 'data' => 'Unfriend successfully.'], 200);
          }

        else
          {
             return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
          }  
    }   





    public function accept_request()
    {

      $input = Input::all();
      $request_id = Input::get('request_id');       
      $user_id=Input::get('user_id'); 
      $notification_id=Input::get('notification_id'); 
      $user_token = Input::get('user_token');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {

         $request_result = Common::single_data($request_id, "friends_request"); 
         if($request_result)
         {
            $friend= $request_result->request_sender;
            $user_id= $request_result->request_receiver;
            $request_id= $request_result->id;
            $data = array(
                  'user_id' => $user_id, 
                  'friend_id' => $friend,              
                  'created_at'=>Carbon::now(),
                  'updated_at'=>Carbon::now()              
                );
          $result_id = Common::insert_data($data, "friends"); 
          $data = array(
                  'user_id' => $friend, 
                  'friend_id' => $user_id,              
                  'created_at'=>Carbon::now(),
                  'updated_at'=>Carbon::now()              
                );
          $result_id = Common::insert_data($data, "friends");
         
          // $data=array(
          //   "status"=>0
          //   );

          // Common::update_data($request_id,$data,"friends_request"); 

             /****************************************************************************/
              //                               Notification
                  

             $notificationto=Common::single_data($request_result->request_receiver,"users");
              $notification=$notificationto->f_name." ".$notificationto->l_name." has accepted your friend request";
                $data=array(
                  "sender"=>$request_result->request_receiver,
                  "receiver"=>$request_result->request_sender,
                  "notification"=>$notification,
                  "method"=>"1",
                  "status"=>"1",
                  "request_id"=>"0",
                  "type"=>'0',
                   "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");

            $data=array(
              'status'=>'0'
             );
             Common::update_data($notification_id,$data,"notifications");      
             $where = array("id" => $request_id);
              $delete_id = Common::delete_record($where, "friends_request"); 
                
             /****************************************************************************/ 
           return response()->json(['code' => 200, 'data' => 'Request has been converted to friends.'], 200);
         }
         else
         {
          return response()->json(['code' => 100, 'data' => 'Invalid Request ID.'], 400);
         }


      } 
      else
      {
        return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
      }
    }
   
  
public function get_info()
{
   $input = Input::all();     
   $user_token = Input::get('user_token');
   $user_id=Input::get('user_id');
    $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){  
          $select=array("id","name");
          $where="status=1";
          $types=Common::search_data($select,$where,"event_types");
          //$data=Common::my_wall_script($user_id,$start_from,$this->limit);
           $select=array("id","name","parent_bit");
          $where="status=1 AND parent_bit=0";
          $privacy=Common::search_data($select,$where,"privacies");
          $where="status=1 AND parent_bit!=0";
          $child=Common::search_data($select,$where,"privacies");
          $data=array(
            'types'=>$types,
            'privacy'=>$privacy,
            'child'=>$child
          );

          return response()->json(['code' => 200, 'data'=>$data], 200);
        }
     else{
       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
}



  public function reject_event()
  {     
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      
      $request_id=Input::get('request_id');
      $notification_id=Input::get('notification_id');

      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){            
          $data_array=array(
            'status'=>0
          );
          Common::update_data($request_id,$data_array,"event_requests");
          $data_array=array(
            'status'=>0
          );
          Common::update_data($notification_id,$data_array,"notifications");

         // $data=Common::my_wall_script($user_id,$start_from,$this->limit);
          return response()->json(['code' => 200, 'data'=>'Event have been successfully rejected'], 200);
        }
     else{
       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
  }


public function event_participants()
  {     
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $start_from=Input::get('start_from');
      $event_id=Input::get('event_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
        $data=Common::all_event_participants($event_id,$start_from,$this->limit);
        $user_data=array();
        for($i=0;$i<count($data);$i++)
        {
        	$friend=0;
        	$select=array("id");
                   $where="(user_id=".$user_id." AND friend_id=".$data[$i]->id.") OR (user_id=".$data[$i]->id." AND friend_id=".$user_id.") AND status=1";
                   $friend_check=Common::search_single_data($select,$where,"friends");
                   if($friend_check)
                   {
                   	 $friend=1;
                   }
            $user_data[]=array(
            	'id'=>$data[$i]->id,
      				'f_name'=>$data[$i]->f_name,
      				'l_name'=>$data[$i]->l_name,
      				'picture'=>$data[$i]->picture,
      				'created_at'=>$data[$i]->created_at,
      				'friend_status'=>$friend
                  );       
        	
        }

         return response()->json(['code' => 200, 'data'=>$user_data], 200);
      }
     else{
       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
   } 


   public function update_msg_counter(){
    $input = Input::all();     
    $user_token = Input::get('user_token');
    $user_id=Input::get('user_id');
  	$conv_id=Input::get('conv_id');
    $where = array("id" => $user_id,"login_token" => $user_token);
    $token=Common::data_by_with($where,"users");
      if($token){        
        $data=array(
          'read_bit'=>1
        );
           $where="read_bit=0 AND receiver_id=".$user_id." AND 	conversation_id=".$conv_id."";
           $messages=Common::update_counter($data,'messages',$where);

           return response()->json(['code' => 200, 'data'=>"Counter has been updated"], 200);
        }
        else{
              return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }  

}






public function send_event_request()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $receiver_id=Input::get('receiver_id'); 
      $event_id=Input::get('event_id');
      $event_privacy=Input::get('event_privacy');
      $event_name=Input::get('event_name'); 
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
          /**************************************************************************/
            if($event_privacy==1){
               return response()->json(['code' => 100, 'data'=>"This Envent is Private"], 200);
               die;   
            }
            elseif($event_privacy==2){
              return response()->json(['code' => 100, 'data'=>"Envent is already Public"], 200);
              die;  
            }
            elseif($event_privacy==3){
              $data=array(
              "event_id"=>$event_id,
              "participants_id"=>$user_id,
              "created_at"=>Carbon::now(),
              "updated_at"=>Carbon::now()
            );
            Common::insert_data($data,"event_participants");
            $where=array('id'=>$event_id);
            Common::increase($where,"total_joins","events");
              $data=array(
                "updated_at"=>Carbon::now()
              );
              $req_id=Common::update_data($event_id,$data, "events"); 
              $sender_record=Common::single_data($user_id,"users");
                $notification=$sender_record->f_name." ".$sender_record->l_name." has Joined your Event ".$event_name."";
                $data=array(
                  "sender"=>$user_id,
                  "receiver"=>$receiver_id,
                  "notification"=>$notification,
                  "method"=>"3",
                  "status"=>"1",
          "type"=>'0',
                  "request_id"=>$req_id,
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");
                return response()->json(['code' => 100, 'data'=>"You Have Joined this Event"], 200);
                die;
            }
            elseif($event_privacy==4){

              $where="event_id=".$event_id." AND sender=".$user_id."";
        $select=array('id');
            $request_detail=Common::search_data_single($select,$where,'event_requests');
            if($request_detail)
            {
            return response()->json(['code' => 100, 'data'=>"You already has requested to join this event"], 200);
               die;
            }


               $data = array(
                    'event_id' => $event_id, 
                    'sender' => $user_id,
                    'receiver'=>$receiver_id,                 
                    'created_at'=>Carbon::now(),
                     'updated_at'=>Carbon::now(),         
                    );
                  $req_id=Common::insert_data($data, "event_requests"); 
               
             /****************************************************************************/
              //                                Notification
              $sender_record=Common::single_data($user_id,"users");
              $notification=$sender_record->f_name." ".$sender_record->l_name." has send you event request for ".$event_name."";
                $data=array(
                  "sender"=>$user_id,
                  "receiver"=>$receiver_id,
                  "notification"=>$notification,
                  "method"=>"3",
                  "request_id"=>$req_id,
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");
             /****************************************************************************/ 
              return response()->json(['code' => 100, 'data'=>"Your Request has sent"], 200);
                die;

            }
            elseif($event_privacy==5){
               return response()->json(['code' => 100, 'data'=>"You cannot Join this Event"], 200);
               die;
            }
            else{
               return response()->json(['code' => 100, 'data'=>"Wrong Selection"], 200);
               die; 
            }

      }
     else{

       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
}



public function reject_event_request()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $request_id=Input::get('request_id'); 
      $notification_id=Input::get('notification_id');
      
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
          $where = array("id" => $request_id);
            $result_id = Common::delete_record($where, "event_requests"); 

            $update = array("status" => '0');
            $result_id = Common::update_data($notification_id,$update, "notifications");
            return response()->json(['code' => 200, 'data'=>"You have Rejected the event request"], 200);
      }
  else{

       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
 }




public function accept_createrevent_request()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $request_id=Input::get('request_id'); 
      $notification_id=Input::get('notification_id');      
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 

          $where="id=".$request_id."";
      $select=array('sender','event_id','receiver');
          $request_detail=Common::search_data_single($select,$where,'event_requests');
          $data=array(
            "event_id"=>$request_detail->event_id,
            "participants_id"=>$request_detail->sender,
            "created_at"=>Carbon::now(),
            "updated_at"=>Carbon::now()
            );
           Common::insert_data($data,"event_participants");
           $where=array('id'=>$request_detail->event_id);
             Common::increase($where,"total_joins","events");
          $where = array("id" => $request_id);
            $result_id = Common::delete_record($where, "event_requests");
            $update = array("status" => '0');
            $result_id = Common::update_data($notification_id,$update, "notifications");
            

            $sender_record=Common::single_data($request_detail->receiver,"users");
                $notification=$sender_record->f_name." ".$sender_record->l_name." has approved your Request";
                $data=array(
                  "sender"=>$request_detail->receiver,
                  "receiver"=>$request_detail->sender,
                  "notification"=>$notification,
                  "method"=>"3",
                  "status"=>"1",
          "type"=>'0',
                  "request_id"=>$request_detail->event_id,
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");

            return response()->json(['code' => 200, 'data'=>"You have Accepted the event request"], 200);
      }
  else{

       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
 }



public function un_join_event()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $event_id=Input::get('event_id');       
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
         $where = array("event_id" => $event_id,"participants_id"=>$user_id);
         $result_id = Common::delete_record($where, "event_participants");         
          $where=array('id'=>$event_id);
          Common::decremental($where,"total_joins","events");
         return response()->json(['code' => 200, 'data'=>"you successfully unjoin the event "], 200);
    }
  else{

       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
 }


public function get_event()
{
   $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $event_id=Input::get('event_id');       
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
          $event_details=Common::single_data($event_id,'events');
           return response()->json(['code' => 200, 'data'=>$event_details], 200);

    }
  else{
       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
}



public function file_uploading(Request $request){

      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $event_id = Input::get('event_id');
      $image = $request->file('image');
    //   if($request->file('image'))
    //   {
    //     echo "Hello";
    //   }
    //   else
    //   {
    //     echo "Again_hello";
    //   }
    // die;
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){
        $imge_name = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/files');
        $image->move($destinationPath, $imge_name);
        $data=array(
          "file"=>url('/')."/public/files/".$imge_name,
          "event_id"=>$event_id,
          "user_id"=>$user_id,
          "created_at"=>Carbon::now(),
          "updated_at"=>Carbon::now()
        );

         Common::insert_data($data,"event_files");
        return response()->json(['code' => 200, 'data'=>'Data have been saved successfully'], 200);  
      }       
      else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  

}


/*******************************************************************************/
/******************************************************************************/
/******************************************************************************/
/******************************************************************************/
/******************************************************************************/
/******************************************************************************/
/******************************************************************************/
/******************************************************************************/

/*
	
public function get_friend_requests(Request $request){
	    $input = Input::all();
      $user_token = Input::get('user_token');
        $user_id=Input::get('user_id');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
        if($token)
         {
      		
      			$where = array("friends_request.request_receiver" => $user_id);
             		$record = Common::get_friend_requests($where, "friends_request");

             		//print_r($record);exit;

             		if($record)
             		{
             			 return response()->json(['code' => 200, 'data' => $record], 200);
             		}
       	 }	
			 else
        {
          return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
        }
}
*/


/***********************************************************************************************/
/**********************************************************************************************/
//                                           17-7-2018
/***********************************************************************************************/
/**********************************************************************************************/


public function main_wall()
  {     
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $start_from=Input::get('start_from');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){  
       	
          $data=Common::main_wall_script($start_from,$this->limit);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
               	{
               		$join_id=1;
               	}
                  
                  $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');
               	$last_followed_by=Common::last_followed_by($data[$i]->event_id);
             
                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "links"=>$event_links,
                  "join_id"=>$join_id,
                  "last_joined"=>$last_followed_by
                );

          }

          return response()->json(['code' => 200, 'data'=>$events], 200);
        }
     else{
       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
  }
  

  public function user_wall()
  {     
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id');
      $start_from=Input::get('start_from');
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
       if($token){  

          if(Input::get('other_user'))
            {
              $user_id=Input::get('other_user');
            }
          $data=Common::my_wall_script($user_id,$start_from,$this->limit);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
			   $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
               	{
               		$join_id=1;
               	}

                 $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');

               		$last_followed_by=Common::last_followed_by($data[$i]->event_id);

                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "links"=>$event_links,
                   "join_id"=>$join_id,
                   "last_joined"=>$last_followed_by
                );
          }


          $where='user_id='.$user_id.' AND status="1"';
          $friend_count=Common::total_count('friends',$where);
          $where='user_id='.$user_id.' AND status="1"';
          $event_count=Common::total_count('events',$where);
          $where='participants_id='.$user_id.' AND status="1"';
          $joined_events_count=Common::total_count('event_participants',$where);

          $result=array(
            'wall'=>$events,
            'friends_counter'=>$friend_count->friends,
            'events_counter'=>$event_count->events,
            'joned_events'=>$joined_events_count->event_participants
          );  

          //echo $friend_count->friends."==".$event_count->events."==".$joined_events_count->event_participants;
          return response()->json(['code' => 200, 'data'=>$result], 200);
        }
     else{
       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
     }  
  }

public function tomorrow_events()
{
	 $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $event_id=Input::get('event_id');   
       $start_from=Input::get('start_from');    
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
      	$data=Common::tomorrow_events_script($start_from,$this->limit);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
               	{
               		$join_id=1;
               	}
                  $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');
                  
                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "links"=>$event_links,
                  "join_id"=>$join_id
                );
          }

          return response()->json(['code' => 200, 'data'=>$events], 200);

		}
	 	else{
	       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
	     }  
}

public function date_wise_events()
{
   $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $date=Input::get('date');          
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
        $data=Common::date_events_script($date,$user_id);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
                {
                  $join_id=1;
                }
                $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');

                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                   "links"=>$event_links,
                  "join_id"=>$join_id
                );
          }

          return response()->json(['code' => 200, 'data'=>$events], 200);

    }
    else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}



public function week_events()
{
	 $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $event_id=Input::get('event_id');
       $start_from=Input::get('start_from');       
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
      	$data=Common::week_events_script($start_from,$this->limit);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
               	{
               		$join_id=1;
               	}
                $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');


                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                   "links"=>$event_links,
                  "join_id"=>$join_id
                );
          }

          return response()->json(['code' => 200, 'data'=>$events], 200);

		}
	 	else{
	       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
	     }  
}



public function location_wise_events()
{
   
	    $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $start_from=Input::get('start_from');
      $lat=Input::get('lat');     
      $long=Input::get('long');
      
      //$lat='32.1579617';     
      //$long='74.024526';

      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 

      	$data=Common::location_wise($start_from,$this->limit,$lat,$long);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
                {
                  $join_id=1;
                }
                 $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');

                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                   "links"=>$event_links,
                  "join_id"=>$join_id
                );
          }

          return response()->json(['code' => 200, 'data'=>$events], 200);
		}
	 	else{
	       return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
	     }  
}

public function search_events()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $start_from=Input::get('start_from');
      $key_word=Input::get('key_word');          
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 

           $data= Common::event_search($start_from,$this->limit,$key_word);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
                {
                  $join_id=1;
                }
                 $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');

             
                $last_followed_by=Common::last_followed_by($data[$i]->event_id);
                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "links"=>$event_links,
                  "join_id"=>$join_id,
                  "last_joined"=>$last_followed_by
                );

               } 

        return response()->json(['code' => 200, 'data'=>$events], 200);  
         
      }   
    else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}



public function live_events()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $start_from=Input::get('start_from');
              
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 
         $data= Common::event_live($start_from,$this->limit);
        $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
                {
                  $join_id=1;
                }
               $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');


                $last_followed_by=Common::last_followed_by($data[$i]->event_id);
                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "join_id"=>$join_id,
                    "links"=>$event_links,
                  "last_joined"=>$last_followed_by
                );

               } 

        return response()->json(['code' => 200, 'data'=>$events], 200);  
      }   
    else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}



public function friends_events()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $start_from=Input::get('start_from');              
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){
      $where="user_id='".$user_id."' AND status='1'";
      $select=array('friend_id');
      //echo $user_name;exit;
      $events=array();
      if($friends=Common::all_friends($select,$where,'friends',$user_id)){
      $string="";
       foreach ($friends as $key => $value) {
          $string.=$value->friend_id.",";
       }
       $all_friends= rtrim($string,",");
       
        $data=Common::friend_filter($user_id,$start_from,$this->limit,$all_friends);

        
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
                {
                  $join_id=1;
                }
                $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');

                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "links"=>$event_links,
                  "join_id"=>$join_id
                );
          }
           return response()->json(['code' => 200, 'data'=>$events], 200);  
        }
        else
        {
          return response()->json(['code' => 200, 'data'=>$events], 200);  
        }
       
      }   
    else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}

public function notify_to_contacts()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $lat=Input::get('lat');
      $long=Input::get('long');              
      // $lat='32.0915439';
      // $long='74.1803305';              
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){
        $data=Common::auto_msg($lat,$long,$user_id);       
       for($i=0;$i<count($data);$i++)
       {
        //echo $data[$i]->user_id."\n";
              $sender_record=Common::single_data($user_id,"users");
              $notification=$sender_record->f_name." ".$sender_record->l_name." is also in your event";
                $data=array(
                  "sender"=>$user_id,
                  "receiver"=>$data[$i]->user_id,
                  "notification"=>$notification,
                  "method"=>"2",
                  "request_id"=>$data[$i]->event_id,
                  "status"=>"1",
                  "type"=>'0',
                  "created_at"=>Carbon::now(),
                  "updated_at"=>Carbon::now()
                );
                Common::insert_data($data,"notifications");
       }
      }     
    else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}




public function search_user_events()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $start_from=Input::get('start_from');
      $key_word=Input::get('key_word');          
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){ 

        if(Input::get('member_id'))
        {
           $user_id=Input::get('member_id');
        }

           $data= Common::event_user_search($start_from,$this->limit,$key_word,$user_id);
          $events=array();
          for ($i=0; $i < count($data); $i++) { 
            $friend_bit=0;
            $join_id=0;
              $where = array("user_id" => $user_id,"friend_id" => $data[$i]->user_id,"status"=>'1');
              if(Common::data_by_with($where,"friends"))
                {
                  $friend_bit=1;
                }
               $where = array("event_id" => $data[$i]->event_id,"participants_id" => $user_id,"status"=>'1');
               if(Common::data_by_with($where,"event_participants"))
                {
                  $join_id=1;
                }
             $where="event_id='".$data[$i]->event_id."'";
                  $select=array('file');
                  $event_links=Common::search_data($select,$where,'event_files');

                $last_followed_by=Common::last_followed_by($data[$i]->event_id);
                $events[]=array(
                  "event_details"=>$data[$i],
                  "friend_bit"=>$friend_bit,
                  "join_id"=>$join_id,
                   "links"=>$event_links,
                  "last_joined"=>$last_followed_by
                );

               } 

        return response()->json(['code' => 200, 'data'=>$events], 200);  
         
      }   
    else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}



  public function forget_password()
  {  

    $input = Input::all();     
    $email = Input::get('email');
     
    $where="email='".$email."'";
    $select=array('id','f_name');
    $check_email=Common::search_data_single($select,$where,'users');   
    if($check_email)
    {
    	
      $string=Common::create_password($check_email->id);
        
           $data = array('password'=>$string, "name" => $check_email->f_name); 
           $to=$email;       
          
           /************************************************/
         //     $content = [
         //    'name'=> 'Umar',
         //    'email'=> 'info@info.com',
         //    'device_type'=> 'Android',
         //    'user_type'=> 'Admin',
         //    'country'=> 'Pakistan',
        	// ];

        Mail::to($to)->send(new SendMail($data));

           /**************************************************/


          // Mail::send('emails.mail', $data, function($message) {
          //       $message->to($to,'Hello')
          //               ->subject('Join App -Reset Password');
          //       $message->from('info@appcrates.com','Appcrates');
          //   });
        

        return response()->json(['code' => 200, 'data'=>"Done"], 200);die;
        
       
    }
    else
    {
      return response()->json(['code' => 100, 'data'=>"Wrong Email"], 200);
    }
  }


public function report_event()
{
      $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $event_id=Input::get('event_id'); 
      $report_type=Input::get('report_type');              
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){

      		$data = array('event_id' => $event_id,
						  'user_id' => $user_id, 
						  'report_type' => $report_type,						
						  'created_at'=>Carbon::now(),
						  'updated_at'=>Carbon::now()			
						);

			$result_id = Common::insert_data($data, "reported_events");
			 $where=array('id'=>$event_id);
			Common::increase($where,"total_reports","events");

      	 return response()->json(['code' => 200, 'data'=>"Report Submited Successfully"], 200);
        }     
        else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}


public function change_open_post()
{
    $input = Input::all();     
      $user_token = Input::get('user_token');
      $user_id=Input::get('user_id'); 
      $notificationid=Input::get('notificationid');       
      $where = array("id" => $user_id,"login_token" => $user_token);
      $token=Common::data_by_with($where,"users");
      if($token){

           $where=array("open"=>'1');
            $record = Common::update_data($notificationid,$where,"notifications"); 

        return response()->json(['code' => 200, 'data'=>"Updated Successfully"], 200);
        }     
        else{
         return response()->json(['code' => 100, 'data'=>"Wrong Token"], 200);
       }  
}


}
