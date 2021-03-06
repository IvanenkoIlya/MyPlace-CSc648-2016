<?php

/*
 * 
 * Author: Ilya
 */

class PageTemplate extends Controller {
    
    protected $user = '';
    
    public function search() {
        $query = "";
        $filters = "";
        $order = 0;
        if( isset( $_POST['query']))    
            $query = $_POST['query'];
        if( isset($_POST['filters']))
            $filters = $_POST['filters'];

        $query_array = explode(" ", $query);
        
        $filters_array = array();

        /* rawurldecode() converts any "%##" (unsafe chars) to its actual value */
        /* parse_str() creates array of key/value pairs based on a URL argument string */
        parse_str(rawurldecode($filters), $filters_array);
        
        /* Remove empty value elements in array */
        $query_array   = array_filter($query_array, 'trim');
//        $filters_array = array_filter($filters_array, 'trim');
        
        /* Minor handling for $filters_array and $query_array */
        foreach ($filters_array as $f_key=>$f_val)
        {
            /* Remove empty value elements in array */
            if ($f_val === ''){
                unset($filters_array[$f_key]);
            }
            
            /* Pass any unique values from $filters to $query */
//            if ((is_numeric($f_val) || is_string($f_val)) &&
//                                     (!in_array ($f_val,$query_array))){
//                array_push($query_array, $f_val);
//            }
        }
        
        
        if (sizeof($filters_array) > 0)
        {
            $temp_array = array_slice($filters_array, 0, 1 );
            $order = $temp_array['order'];
        }

        $apartments = $this->apartment_db->search( $query_array, $filters_array, $order);
        
        $results = "";
        if( !$apartments) {
            $results = "No Results!";
        } else {
            $results .= '<div class="pull-right">Total apartments:' . count( $apartments) . ' </div><br><br><div class="row">';
            $results .=  $this->formatApartment( $apartments);   
        }

        echo $results;
    }
    
    public function formatApartment($apartments) {
        $results = "";
        $i = 0; 
        foreach ( $apartments as $apartment) {
            $i = $apartment->id;
            $results .= '<div class="col-sm-4 col-lg-4 col-md-4">
                    <div class="thumbnail">';
            if( isset( $apartment->thumbnail)){
                $results .= '<img src="data:image/jpeg;base64,'.base64_encode( $apartment->thumbnail).'" alt="" style="cursor: pointer;height:150px;width:320px;" data-toggle="modal" data-target="#aptModal">'; 
            } else {
                $results .= '<img src="http://placehold.it/320x150" alt="" style="cursor: pointer;height:150px;width:320px;" data-toggle="modal" data-target="#aptModal">';
            }
            $results .= '<div class="caption">';
            if( isset( $apartment->actual_price)) $results .= '<h4 class="pull-right">$'. htmlspecialchars( $apartment->actual_price).'</h4>';
            if( isset( $apartment->title)) {
                $title = htmlspecialchars( $apartment->title);
                if( strlen( $title) > 13) {
                    $title_array = explode( " ", $title);
                    $title = "";
                    if( strlen( $title_array[ 0]) > 13) {
                        $title = substr( $title_array[ 0], 0, 13) . "...";
                    } else {
                        foreach( $title_array as $word) {
                            if( strlen( $title) + strlen( $word) > 13) {
                                $title .= "...";
                                break;
                            } else {
                                $title .= $word . " ";
                            }    
                        }
                    }
                }
                if(strlen($title) == 0)
                    $title = $apartment->id;
            } else {
                $title = $apartment->id;
            }
            $results .= '<h4><a href="" data-toggle="modal" data-target="#aptModal">'.$title.'</a></h4>
                             <ul class="columns" data-columns="2">';
            if( isset( $apartment->rental_term)) $results .= '<li>Rent term: '.htmlspecialchars( $apartment->rental_term).'</li>';
            if( isset( $apartment->bedroom)) $results .= '<li>Bedrooms: '.htmlspecialchars( $apartment->bedroom).'</li>';
            if( isset( $apartment->area_code)) $results .= '<li>Zip code: '.htmlspecialchars( $apartment->area_code).'</li>';
            $results .= '</ul>
                        </div>
                        <div class="ratings">';
            if( isset($_COOKIE["myPlace_userType"]) && $_COOKIE["myPlace_userType"] == 0) {
                $results .= '<button type="button" class="btn btn-success btn-sm pull-right contact-button" data-toggle="modal" data-target="#contactLandlord'.$i.'">Rent now</button>';
            } else {
                $results .= '<button type="button" class="btn btn-success btn-sm pull-right contact-button" data-toggle="modal" data-target="#contactLandlord'.$i.'" style="display: none;">Rent now</button>';
            }
            $results .= '<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#aptModal'.$i.'" onclick="createMap('.$i.','.$apartment->area_code.')" id="openApt'.$i.'">Details</button>
                            <div class="modal fade" id="aptModal'.$i.'" role="dialog" style="color: #000">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Details on Apartment</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="row">
                                                    <div class="row-height">
                                                        <div class="col-sm-6 col-height">
                                                         <!--http://www.bootply.com/XiWUwjbGtB -->
                                                            <!-- main slider carousel -->
                                                            <div class="row">
                                                                <div class="col-md-12" id="slider">
                                                                    <div class="col-md-12" id="carousel-bounding-box">
                                                                        <div id="myCarousel" class="carousel slide">
                                                                            <!-- main slider carousel items -->
                                                                            <div class="carousel-inner">';
            $images = $this->apartment_db->getImageDB( $apartment->id);
            $j = 0;
            
            foreach( $images as $image) {
                if( $j == 0) {
                    $results .= '<div class="active item" data-slide-number="'.$j.'"><img src="data:image/jpeg;base64,'.base64_encode( $image->image).'" class="img-responsive" style="width:1200px;height:480;"></div>';
                } else {
                    $results .= '<div class="item" data-slide-number="'.$j.'"><img src="data:image/jpeg;base64,'.base64_encode( $image->image).'" class="img-responsive" style="width:1200px;height:480;"></div>';
                }
                $j++;
            }
            
            $results .= '</div>
                                                                            <!-- main slider carousel nav controls --> <a class="carousel-control left" href="#myCarousel" data-slide="prev">‹</a>
                                                                            <a class="carousel-control right" href="#myCarousel" data-slide="next">›</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!--/main slider carousel-->
                                                            <br>
                                                            <!-- thumb navigation carousel -->
                                                            <div class="col-md-12 hidden-sm hidden-xs" id="slider-thumbs">
                                                                <!-- thumb navigation carousel items -->
                                                                <ul class="list-inline">';
            
            $j = 0;
            foreach( $images as $image) {
                if( $j == 0){
                    $results .= '<li> <a id="carousel-selector-'.$j.'" class="selected"><img src="data:image/jpeg;base64,'.base64_encode( $image->image).'" class="img-responsive" style="height:80px;width:60px;"></a></li>';
                } else {    
                    $results .= '<li> <a id="carousel-selector-'.$j.'"><img src="data:image/jpeg;base64,'.base64_encode( $image->image).'" class="img-responsive" style="height:80px;width:60px;"></a></li>';
                }
                $j++;
            }    
            
            $results .= '</ul>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-height col-top">';
            if( isset( $apartment->title)) 
                $results .= '<h1>'. htmlspecialchars( $apartment->title).'</h1>';
            else
                $results .= '<h1> Description </h1>';
            if( isset( $apartment->description)) $results .= '<p align ="justify" style="padding-right: 20px">'
                                                            . htmlspecialchars( $apartment->description) .    
                                                            '</p>';                                                        
            $results .= '</div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6 col-height">
                                                    <h1> Map </h1>
                                                    <div id="map'.$i.'" style="width:400px;height:400px;background:yellow"></div>
                                                </div>

                                                <div class="col-sm-6 col-height">

                                                    <h1> Amenities/Filtered </h1>
                                                    <ul style="font-size: 16px">';
            if( isset( $apartment->actual_price)) $results .= '<li> <strong>Price: </strong>$'. htmlspecialchars( $apartment->actual_price) .'</li>';
            if( isset( $apartment->bedroom)) $results .= '<li> <strong>Bedrooms: </strong>'. htmlspecialchars( $apartment->bedroom) .'</li>';
            if( isset( $apartment->area_code)) $results .= '<li> <strong>Zip code: </strong>'. htmlspecialchars( $apartment->area_code) .'</li>';
//            if( isset( $apartment->)) $results .= '<li> <strong>Distance: </strong>'..'</li>';
            if( isset( $apartment->rental_term)) $results .= '<li> <strong>Availability term: </strong>'. htmlspecialchars( $apartment->rental_term) .'</li>';

            
            if( isset( $apartment->furnished)) {
                $results .= '<li> <strong>Furnished: </strong>';
                if( $apartment->furnished == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';
            }
            
            if( isset( $apartment->laundry)) {
                $results .= '<li> <strong>Laundry: </strong>';
                if( $apartment->laundry == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';
            }
            
            if( isset( $apartment->parking)) {
                $results .= '<li> <strong>Parking available: </strong>';
                if( $apartment->parking == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';    
            }
            
            if( isset( $apartment->pet_friendly)) {
                $results .= '<li> <strong>Pet friendly: </strong>';
                if( $apartment->actual_price == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';
            }
            
            if( isset( $apartment->shared_room)) {
                $results .= '<li> <strong>Shared Room: </strong>';
                if( $apartment->shared_room == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';
            }
            
            if( isset( $apartment->smoking)) {
                $results .= '<li> <strong>Smoking: </strong>';
                if( $apartment->smoking == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';
            }
            
            if( isset( $apartment->wheel_chair_access)) {
                $results .= '<li> <strong>Wheelchair Access: </strong>';
                if( $apartment->wheel_chair_access == 0)
                    $results .= htmlspecialchars ( 'No');
                else
                    $results .= htmlspecialchars ( 'Yes');
                $results .= '</li>';
            }
            
            
//            if( isset( $apartment->actual_price)) $results .= '<li> <strong>Tags: </strong>Spacious, comfy, inviting</li';>
            $results .= '</ul>

                                                    
                                                    <p>
                                                        <br>
                                                        Or need more information before renting apartment? Contact landlord below.
                                                        <br>
                                                    </p>';
                                             
            if( isset($_COOKIE["myPlace_userType"]) && $_COOKIE["myPlace_userType"] == 0) {
                $results .= '<button type="button" class="btn btn-info btn-sm contact-button" data-toggle="modal" data-target="#contactLandlord'.$i.'">Contact Landlord</button>';
            } else {
                $results .= '<button type="button" class="btn btn-info btn-sm contact-button" data-toggle="modal" data-target="#contactLandlord'.$i.'" style="display: none;">Contact Landlord</button>';
            }
                                                    
            $results .= '</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="contactLandlord'.$i.'" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="contactLandlordLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                            <h3 id="myModalLabel">Contact form</h3>
                                        </div>
                                        <div class="modal-body">';
            $results .= '<form method="POST" onsubmit="sendMessage( event,'.$i.')" class="form-horizontal col-sm-12" id="send-message-form'.$i.'">
                            <div class="form-group">
                                <label>Message</label>
                                <textarea class="form-control" name="Message" placeholder="Your message here.." data-placement="top" data-trigger="manual" rows="5"></textarea>
                            </div>';
            
            if(isset ($apartment->id)) $results .= '<div class="form-group">
                     <input type="hidden" name="aid" value="' . $apartment->id . '"></div>';
            if(isset ($apartment->user_id)) $results .= '<div class="form-group">
                    <input type="hidden" name="messageRecipient" value="' . $apartment->user_id.'"></div>'; 
                     
            $results .= '<input type="submit" class="btn btn-success pull-right" value="Send It!">
                <p class="help-block pull-left text-danger hide" id="form-error">&nbsp; The form is not valid. </p>
                <button class="btn pull-left" data-dismiss="modal" data-target="#contactLandlord" aria-hidden="true">Cancel</button>';
            $results .= '</form>
                                        </div>
                                        <div class="modal-footer">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>'; 
//            $i++;
        }
        
//        $results .= '<div class="col-sm-4 col-lg-4 col-md-4">
//                    <p>Click below to view more results</p>
//                    <a class="btn btn-primary">More results</a>
//                </div>';
        return $results;
    }
    
    public function login() {
        $results = "login default";
        if( isset( $_POST['userinfo']))
            $results = $_POST['userinfo'];
        
        $results_array = array();
        parse_str(rawurldecode( $results), $results_array);
        
        $results = $this->user_db->hasUser( $results_array["Email"]);
        
        if( $results) { // if user exists in db
            $user = new User( $results->id, $results->email, $results->name, $results->password, $results->usertype);
            if( password_verify( $results_array['Password'], $results->password)) { //passsword is correct
                setcookie( "myPlace_userID", $user->getID(), time() + (84600 * 7), '/'); // create a login cookie that expires after a week
                setcookie( "myPlace_userType", $user->getType(), time() + (84600 * 7), '/');
                setcookie( "myPlace_user", $user->getName(), time() + (84600 * 7), '/');
                $this->user = $user->getName();
                $results = $this->formatLogin();
            } else {
                // wrong password error
                $results = "Error-WPW";
            }
        } else { // user not found in db
            // user not found error
            $results = "Error-UNF";
        } 
        
        echo $results;
    }
    
    public function logout() {
        $user = '';
        if( isset($_COOKIE["myPlace_user"])) { //unload the cookies and expire them
            unset($_COOKIE["myPlace_user"]);
            setcookie( "myPlace_user", 'none', time() - 3600, '/'); 
        }
        
        if( isset($_COOKIE["myPlace_userID"])) {
            unset($_COOKIE["myPlace_userID"]);
            setcookie( "myPlace_userID", 'none', time() - 3600, '/');
        }
        
        if( isset($_COOKIE["myPlace_userType"])) {
            unset($_COOKIE["myPlace_userType"]);
            setcookie( "myPlace_userType", 'none', time() - 3600, '/');
        }
        echo $this->formatLogout();    
    }
    
    protected function validateEmail( $email) {
        $email_validation = explode( '@', $email);
        return $email_validation[1] == "mail.sfsu.edu" ? $email: false;   
    }
    
    protected function formatLogin() {
        return '<a id="ajax_logout" onclick="logout()" data-toggle="tooltip" data-placement="bottom" title="Logout"><span class="glyphicon glyphicon-log-out"></span> Welcome ' . $this->user . '</a>';        
    }
    
    protected function formatLogout() { 
        return '<a href="#signup" data-toggle="modal" data-target=".bs-modal-sm" ><span class="glyphicon glyphicon-log-in"></span> Log in/Sign up</a>';
    }
}
