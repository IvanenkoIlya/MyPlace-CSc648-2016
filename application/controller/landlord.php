<?php

/**
 * Description of landlord
 *
 * @author Jimmy
 */
class Landlord extends PageTemplate{
    
    public function index()
    {
        // load views
//        require APP . 'view/_templates/header.php';
//        require APP . 'view/post/mypost.php';
//        require APP . 'view/_templates/footer.php';
    }
    
    /**
     * This method corresponds to the 'Add Apartment Form' from mypost.php. This
     * method is called via POST and will add a new Apartment record to the
     * database based on the POST-ed data.
     * 
     * @return \Apartment - Apartment object made from the POST-ed data.
     * @throws Exception - if the query/upload fails.
     */
    public function addApartment()
    {
        
        /* ---------------------------------------------------------------------
         * TODO: DELETE Dummy landlord user in place of actual implemented user.
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         */
                require_once APP . 'test/TEST.php';
                $user       = TEST::getLocalDummyLandlordUser();
                $userType = 0;
                if( isset($_COOKIE["myPlace_userType"]))
                    $userType = $_COOKIE["myPlace_userType"];
                if( isset($_COOKIE["myPlace_userID"]))
                    $userID = $_COOKIE["myPlace_userID"];
                
                //['Name'] ['Email'] ['Number'] from <form> should be set automatically..
                //
                // <UNTESTED possible alternative for if $user object is not persistent? ....>
                // session_start(); //required at top of every page or put in Parent page.
                // if (isset($_SESSION['login_id'])  //=== true ...
                //      $user_id     = $_SESSION['login_id'];
                //      $userObject  = $this->user_db->hasUser($user_id); //get valid user.
                //      
                // if (is_a($userObject, 'User') && ($userObject->getType() === 1))
                //      ... perform $this->addApartment() ...
                
        /* ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * TODO: DELETE Dummy landlord user in place of actual implemented user.
         * ---------------------------------------------------------------------
         */
                
        
        /* Variables */
        $apartForm      = array();          //holds the 'Add Apartment Form' data. 
        $apartImages    = array();          //holds the images sent from the form.
        $errorMsgs      = array();          //holds validation response messages.
        $apartment      = new Apartment();  //Apartment object to add to database.
        
        /* Form <input name=""> for quick reference */
        $name_bedroom       = "Bedroom";
        $name_price         = "Price";
        $name_startTerm     = "StartTerm";
        $name_endTerm       = "EndTerm";
        $name_zipcode       = "ZipCode";
        $name_description   = "Description";
        $name_petFriendly   = "PetFriendly";
        $name_parking       = "Parking";
        $name_laundry       = "Laundry";
        $name_smoking       = "Smoking";
        $name_sharedRoom    = "SharedRoom";
        $name_furnished     = "Furnished";
        $name_wheelChairAcc = "WheelChairAccess";
        $name_images        = "images";
        
        /* Verify that the User is a landlord (i.e usertype == 1 == landlord.) */
        if ($userType != 1) {
            $errorMsgs['Failure'] = "You must be a landlord who's signed in to add a new apartment!";
        }
        
        /* Return if no form data was sent over */
        if ($_POST){ 
            $apartForm = array_filter($_POST);
        } else {
            $errorMsgs['Error']  = "Error: form data was not received!";
            $errorMsgs['Result'] = "Failure: cannot add new apartment at this time!";
            echo json_encode($errorMsgs);
            return;
        }
        
        /* Have $apartForm['image'] set to image files array passed over */
        if (isset($_FILES[$name_images]['tmp_name']) && (!empty($_FILES[$name_images]['tmp_name']))) 
        {
            $apartImages = array_filter($_FILES[$name_images]['tmp_name']);
        }
        
        /* Create new Apartment object acccording to the form values */
        try {
            
            /* Set the user ID of this Apartment to this logged in landlord's ID */
            $apartment->setUserID($userID);
            
            /* ---------Set number of bedrooms in this Apartment------------ */
            if ((isset($apartForm[$name_bedroom])) 
                                    && (is_numeric($apartForm[$name_bedroom])) 
                                              && !($apartForm[$name_bedroom] < 0)) {
                $apartment->setBedRoomCount($apartForm[$name_bedroom]);
            } else {
                $errorMsgs[$name_bedroom] = "Enter a positive number of bedrooms";
            }

            /* ----------Set the price of this Apartment--------------------- */
            $priceRegex = "/^(\d*)\.?\d{0,2}$/";
            if ((isset($apartForm[$name_price])) 
                            && (is_numeric($apartForm[$name_price]))
                            && (preg_match($priceRegex, $apartForm[$name_price]))
                                                   && !($apartForm[$name_price] < 0)) {
                
                $apartment->setActualPrice($apartForm[$name_price]);
            } else {
                $errorMsgs[$name_price] = "Enter a price following any of these "
                                        . "number formats: ## or (many #).## or .## ";
            }

            /* ---------Set the starting renting term of this Apartment------ */
            if ((isset($apartForm[$name_startTerm])) 
                    && DateTime::createFromFormat('Y-m-d', $apartForm[$name_startTerm])) {

                $apartment->setBeginTerm($apartForm[$name_startTerm]);
            } else {
                $errorMsgs[$name_startTerm] = "Enter a valid date in the format:"
                                            . " mm/dd/yyyy";
            }

            /* ---------Set the end of the renting term of this Apartment---- */
            if ((isset($apartForm[$name_endTerm])) 
                        && DateTime::createFromFormat('Y-m-d', $apartForm[$name_endTerm])) {

                $apartment->setEndTerm($apartForm[$name_endTerm]);
            } else {
                $errorMsgs[$name_endTerm] = "Enter a valid date in the format:"
                                          . " mm/dd/yyyy";
            }

            /* ---------Set the zipcode of this Apartment-------------------- */
            if ((isset($apartForm[$name_zipcode])) 
                                    && is_numeric($apartForm[$name_zipcode]) 
                                             && !($apartForm[$name_zipcode] < 0)
                                             &&  (strlen($apartForm[$name_zipcode]) == 5)) {

                $apartment->setAreaCode($apartForm[$name_zipcode]);
            } else {
                $errorMsgs[$name_zipcode] = "Enter a valid 5 digit zipcode";
            }

            /* ---------Set the rest of the form data------------------------ */
            if (isset($apartForm[$name_description]))   { $apartment->setDescription($apartForm[$name_description]);}
            if (isset($apartForm[$name_petFriendly]))   { $apartment->setPetFriendly($apartForm[$name_petFriendly]);}
            if (isset($apartForm[$name_parking]))       { $apartment->setParking    ($apartForm[$name_parking]);    }
            if (isset($apartForm[$name_laundry]))       { $apartment->setLaundry    ($apartForm[$name_laundry]);    }
            if (isset($apartForm[$name_smoking]))       { $apartment->setSmoking    ($apartForm[$name_smoking]);    }
            if (isset($apartForm[$name_sharedRoom]))    { $apartment->setSharedRoom ($apartForm[$name_sharedRoom]); }
            if (isset($apartForm[$name_furnished]))     { $apartment->setFurnished  ($apartForm[$name_furnished]);  }
            if (isset($apartForm[$name_wheelChairAcc])) { $apartment->setWheelChairAccess($apartForm[$name_wheelChairAcc]); }
            
            foreach ($apartImages as $filename){
                if (!empty(trim($filename))){
                    if ($apartment->getImagesCount() === 10) {
                        $errorMsgs[$name_images] = "The number of images exceeded 10";
                        break;
                    } else {
                        $apartment->addImage(file_get_contents($filename));
                    }
                } 
            }
            
            /* If not error messages thus far then add Apartment to database */
            if (empty($errorMsgs)) {
                /* Add apartment to Apartment database */
                $this->apartment_db->addApartment($apartment);
                $errorMsgs['Result'] = "Apartment has been successfully added!";
            }
            
        } catch (Exception $exception) {
            $errorMsgs['Error']  = $exception->getMessage();
            $errorMsgs['Result'] = "Failure: cannot add new apartment at this time!";
            echo json_encode($errorMsgs);
            return;
        }
        
        echo json_encode($errorMsgs);
        
        return $apartment;
    }
    
}