<?php

include_once(__DIR__ . '/../Model/config.php');
include_once(__DIR__ . '/../Model/user.php');

class Controller_user {

    
    public function add_user(User $user) {

        $sql = "INSERT INTO user (
            name_user,
            lastname_user,
            email_user,
            password_user,
            phone_user,
            gender_user,
            birthday_user,
            height_user,
            weight_user,
            bmi_user,
            activitylvl_user,
            illness_user,
            allergie_user,
            medicament_user,
            inscriptiondate_user,
            role_user,
            subscription_user,
            account_state_user,
            duration_user
        ) VALUES (
            :name,
            :lastname,
            :email,
            :password,
            :phone,
            :gender,
            :birthday,
            :height,
            :weight,
            :bmi,
            :activity,
            :illness,
            :allergie,
            :medicament,
            :date,
            :role,
            :subscription,
            :account_state,
            :duration
        )";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);

            $params = [
                'name'      => $user->getNameUser(),
                'lastname'  => $user->getLastnameUser(),
                'email'     => $user->getEmailUser(),
                'password'  => $user->getPasswordUser(),
                'phone'     => $user->getPhoneUser(),
                'gender'    => $user->getGenderUser(),
                'birthday'  => $user->getBirthdayUser(),
                'height'    => $user->getHeightUser(),
                'weight'    => $user->getWeightUser(),
                'bmi'       => $user->getBmiUser(),
                'activity'  => $user->getActivitylvlUser(),
                'illness'   => $user->getIllnessUser(),
                'allergie'  => $user->getAllergieUser(),
                'medicament'=> $user->getMedicamentUser(),
                'date'      => $user->getInscriptiondateUser(),
                'role'      => $user->getRoleUser(),
                'subscription' => $user->getSubscriptionUser(),
                'account_state' => $user->getAccountStateUser(),
                'duration' => $user->getDurationUser()
            ];

            error_log("======== INSERT PARAMS ========");
            error_log("Email: " . $params['email']);
            error_log("Password: " . $params['password']);
            error_log("Password length: " . strlen($params['password']));
            error_log("======== END PARAMS ========");

            $result = $query->execute($params);

            if (!$result) {
                $errorInfo = $query->errorInfo();
                throw new Exception("Database insert failed: " . implode(" - ", $errorInfo));
            }

            error_log("User inserted successfully. Rows affected: " . $query->rowCount());
            return true;

        } catch (Exception $e) {
            error_log('Database error in add_user: ' . $e->getMessage());
            throw $e;
        }
    }


    public function list_users() {

        $sql = "SELECT * FROM user";
        $db = config::getConnexion();

        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function listusers() {
        return $this->list_users();
    }

    public function search_users(string $term) {

        $sql = "SELECT * FROM user
                WHERE CAST(id_user AS CHAR) LIKE :term
                   OR name_user LIKE :term
                   OR lastname_user LIKE :term
                   OR email_user LIKE :term
                   OR phone_user LIKE :term
                   OR role_user LIKE :term
                ORDER BY id_user DESC";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['term' => '%' . $term . '%']);
            return $query;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }


    public function delete_user($id) {

        $sql = "DELETE FROM user WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }


    
    public function get_user($id) {

        $sql = "SELECT * FROM user WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }


    
    public function update_user(User $user, $id) {

        $sql = "UPDATE user SET
            name_user = :name,
            lastname_user = :lastname,
            email_user = :email,
            password_user = :password,
            phone_user = :phone,
            gender_user = :gender,
            birthday_user = :birthday,
            height_user = :height,
            weight_user = :weight,
            bmi_user = :bmi,
            activitylvl_user = :activity,
            illness_user = :illness,
            allergie_user = :allergie,
            medicament_user = :medicament,
            inscriptiondate_user = :date,
            role_user = :role,
            subscription_user = :subscription,
            account_state_user = :account_state,
            duration_user = :duration
            WHERE id_user = :id";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);

            $query->execute([
                'name'       => $user->getNameUser(),
                'lastname'   => $user->getLastnameUser(),
                'email'      => $user->getEmailUser(),
                'password'   => $user->getPasswordUser(),
                'phone'      => $user->getPhoneUser(),
                'gender'     => $user->getGenderUser(),
                'birthday'   => $user->getBirthdayUser(),
                'height'     => $user->getHeightUser(),
                'weight'     => $user->getWeightUser(),
                'bmi'        => $user->getBmiUser(),
                'activity'   => $user->getActivitylvlUser(),
                'illness'    => $user->getIllnessUser(),
                'allergie'   => $user->getAllergieUser(),
                'medicament' => $user->getMedicamentUser(),
                'date'       => $user->getInscriptiondateUser(),
                'role'       => $user->getRoleUser(),
                'subscription' => $user->getSubscriptionUser(),
                'account_state' => $user->getAccountStateUser(),
                'duration' => $user->getDurationUser(),
                'id'         => $id
            ]);

        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

}  

?>