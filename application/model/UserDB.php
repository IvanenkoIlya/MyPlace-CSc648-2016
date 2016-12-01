<?php

require APP . 'libs/User.php';

/**
 * UserDB is responsible for handling User
 *
 * @author Jimmy
 */
class UserDB {
    
    /**
     * @param object $db A PDO database connection
     */
    public function __construct($db)
    {
        try {
            $this->db = $db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }
    
    /**
     * Add the given new User into the User database table.
     * 
     * @throws Exception - if the INSERT query fails.
     * @param User $user - the user account to add.
     * @return boolean $isAdded - true means successfully added, false if not.
     */
    public function addUser(User $user)
    {
        $isAdded = false;    //return this boolean
        
        $sql    = " INSERT INTO User
                    (  name,  email,  password,  usertype)
                    VALUES
                    ( :name, :email, :password, :usertype) ";
        
        
        if (! $this->hasUser($user->getEmail()) )
        {
            /* Prepare and Execute INSERT statement for adding a new User */
            $stmt   = $this->db->prepare($sql);
            $result = $stmt->execute(array( "name" => $user->getName(),
                                            "email" => $user->getEmail(),
                                            "password" => $user->getPassword(),
                                            "usertype" => $user->getType()));
            if ($result === false)
                {throw new Exception('UserDB->addUser() - Could not execute INSERT User query'); }
                
            $isAdded = true;
        }
            
        return $isAdded;
        
    }
    
    /**
     * Check if the given email belongs to any existing user account.
     * 
     * @param String $email - the user email to check for uniqueness.
     * @return mixed - Returns the User record if the email exists, else false.
     */
    public function hasUser($email)
    {
        $sql = " SELECT * FROM User 
                 WHERE  email = :email ";
        
        
        /* Prepare and Execute SELECT statement for email verification */
        $stmt    = $this->db->prepare($sql); 
        $result  = $stmt->execute(array( 
                                    "email" => $email
                                ));
        if ($result === false)
            {throw new Exception('UserDB->hasUser - Could not execute SELECT User query'); }
                
        /* should return 1 result or 0 due to SQL unique constraint */
        //NOTE: this is using fetch() not fetchAll() [no foreach needed]
        $account  = $stmt->fetch();
        
        return ($account) ? $account : false;
        
    }
    
}
