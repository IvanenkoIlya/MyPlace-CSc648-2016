<?php

class Post extends Controller
{
    public function index()
    {
        // load views
        $apartments = $this->model->getApartmentDB();

        require APP . 'view/_templates/header.php';
        require APP . 'view/post/mypost.php';
        require APP . 'view/_templates/footer.php';
    }
    
    public function postPage()
    {
        // load views
        require APP . 'view/_templates/header.php';
        require APP . 'view/post/mypost.php';
        require APP . 'view/_templates/footer.php';
    }
    
   
    public function displayApartments()
    {
        $result = '';
        return $result;
    }
}

