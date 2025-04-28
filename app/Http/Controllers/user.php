<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Botman\Botman\Botman;
use Botman\Botman\Messages\Incoming\Answer;
class user extends Controller
{

    function signup(Request $request){
        $name=$request->input('name');      
        $email=$request->input('email');        
        $password=$request->input('password');     
        $password2=$request->input('cpassword'); 
        DB::table('user')->insert([
          "user_name"=>$name,
          "user_email"=>$email,
           "user_pass"=>$password,
        ]);
        return redirect()->Back()->with('Success','SignUp Successfully');
        }

    function login(request $request){
        $email=$request->input('email');      
        $pass=$request->input('password');  

        $user=DB::table('user')          
              ->where('user_email', $email)
              ->where('user_pass', $pass)
              ->first();

            if($user){
            //  echo "Login Valid";
            //  session(['Semail'=>$user->reg_email]);
             session(['Sname'=>$user->user_name]);

             return redirect()->Back()->with('Success','Login Successfully');
            }
            else{
              return redirect()->Back()->with('Fail','Login Failed');
            }

        }

        function logout(){
            Session::flush();
            return redirect('/login');
          }

         
            function add_product(Request $request){
              $pname=$request->input('p_name');      
              $pprice=$request->input('p_price');        
              $pdesc=$request->input('p_desc');        
          
              DB::table('product')->insert([
                "product_name"=>$pname,
                "product_price"=>$pprice,
                "product_desc"=>$pdesc,
                 
                 
              ]);
              return redirect()->Back()->with('Success','Product Added Successfully');
            }

            function menu_view(){
              $menuitem=DB::table('product')          
              ->get();
              //echo $user;
              return view('shop',compact('menuitem'));
            }

            public function addcart($p_id)
            {
              $mid = $p_id;
              $user = DB::table('cart')
              ->where('product_id',$mid)
              ->first();
  
              if(!$user){
                DB::table('cart')->insert([
                  'product_id'=>$mid,
                ]);
                return redirect()->Back()->with('Success','cart Sucessfully');
              }
              else{
                return redirect()->Back()->with('Fail','Already in your cart');
              }
            
              }

              function view_cart(){
               
                $menuitem = DB::table('cart')
                  ->join('product', 'cart.product_id', '=', 'product.product_id')
                  ->select('cart.*','product.*')
                  ->get();
                
                  return view('cart',compact('menuitem'));
            
              }

              function plus($cid,$qty){
                $newqty=$qty+1;
                $user=DB::table('cart')
                ->where('cart_id',$cid)
                ->update(['qty'=>$newqty]);
  
                if($user){
                  return redirect()->Back()->with('Success','Quantity Upated Sucessfully');
                }
                else{
                  return redirect()->Back()->with('Fail','Not Updated');
                }
              }

              function minus($cid,$qty){
                if($qty > 1){
                      $newqty=$qty-1;
                      $user=DB::table('cart')
                      ->where('cart_id',$cid)
                      ->update(['qty'=>$newqty]);
  
                      if($user){
                        return redirect()->Back()->with('Success','Quantity Upated Sucessfully');
                      }
                      else{
                        return redirect()->Back()->with('Fail','Not Updated');
                      }
              } 
              else{
                return redirect()->back()->with('Fail', 'Quantity cannot be less than 1');
            
                }
              }

              function deletecart($cid){

                $user=DB::table('cart')
                ->where('cart_id',$cid)
                ->delete();
  
                if($user){
                  return redirect()->Back()->with('Success','Item Removed Sucessfully');
                }
                else{
                  return redirect()->Back()->with('Fail','Item Removed Unsucessfully');
                }
  
  
  
              } 
              public function handle()
              {
                 
                  $botman = app('botman');
             
                  $botman->hears('{message}', function($botman, $message) {
             
                      if ($message == 'hi') {
                          $this->askName($botman);
                      }
                      
                      else{
                         // $botman->reply("Start a conversation by saying hi.");
                         $faqData = json_decode(file_get_contents(storage_path('app/faq.json')), true);
                      
                         // Search for the question in the FAQ data
                         foreach ($faqData as $faq) {
                             if (stripos($faq['question'], $message) !== false) {
                                 $botman->reply($faq['answer']);
                                 return;
                             }
                         }
                         $botman->reply("Sorry, I couldn't find an answer to your question.");
                      }
             
                  });
             
                  $botman->listen();
              }
              public function askName($botman)
              {
                  $botman->ask('Hello! What is your Name?', function(Answer $answer) {
             
                      $name = $answer->getText();
             
                      $this->say('Nice to meet you '.$name);
                  });
              }
}