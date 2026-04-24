<?php

class User
{
    private int $id_user;
    private string $name_user;
    private string $lastname_user;
    private string $email_user;
    private string $password_user;
    private string $phone_user;
    private string $gender_user;
    private string $birthday_user;
    private int $height_user;
    private int $weight_user;
    private int $bmi_user;
    private string $activitylvl_user;
    private string $illness_user;
    private string $allergie_user;
    private string $medicament_user;
    private string $inscriptiondate_user;
    private string $role_user;
    private string $subscription_user;
    private string $account_state_user;
    private string $duration_user;

    
    public function __construct(
        int $id_user,
        string $name_user,
        string $lastname_user,
        string $email_user,
        string $password_user,
        string $phone_user,
        string $gender_user,
        string $birthday_user,
        int $height_user,
        int $weight_user,
        int $bmi_user,
        string $activitylvl_user,
        string $illness_user,
        string $allergie_user,
        string $medicament_user,
        string $inscriptiondate_user,
        string $role_user,
        string $subscription_user = 'normal',
        string $account_state_user = 'active',
        string $duration_user = '00:00:00'
    ) {
        $this->id_user = $id_user;
        $this->name_user = $name_user;
        $this->lastname_user = $lastname_user;
        $this->email_user = $email_user;
        $this->password_user = $password_user;
        $this->phone_user = $phone_user;
        $this->gender_user = $gender_user;
        $this->birthday_user = $birthday_user;
        $this->height_user = $height_user;
        $this->weight_user = $weight_user;
        $this->bmi_user = $bmi_user;
        $this->activitylvl_user = $activitylvl_user;
        $this->illness_user = $illness_user;
        $this->allergie_user = $allergie_user;
        $this->medicament_user = $medicament_user;
        $this->inscriptiondate_user = $inscriptiondate_user;
        $this->role_user = $role_user;
        $this->subscription_user = $subscription_user;
        $this->account_state_user = $account_state_user;
        $this->duration_user = $duration_user;
    }

   
    public function __destruct()
    {
       
    }

    
    public function getIdUser(): int { return $this->id_user; }
    public function getNameUser(): string { return $this->name_user; }
    public function getLastnameUser(): string { return $this->lastname_user; }
    public function getEmailUser(): string { return $this->email_user; }
    public function getPasswordUser(): string { return $this->password_user; }
    public function getPhoneUser(): string { return $this->phone_user; }
    public function getGenderUser(): string { return $this->gender_user; }
    public function getBirthdayUser(): string { return $this->birthday_user; }
    public function getHeightUser(): int { return $this->height_user; }
    public function getWeightUser(): int { return $this->weight_user; }
    public function getBmiUser(): int { return $this->bmi_user; }
    public function getActivitylvlUser(): string { return $this->activitylvl_user; }
    public function getIllnessUser(): string { return $this->illness_user; }
    public function getAllergieUser(): string { return $this->allergie_user; }
    public function getMedicamentUser(): string { return $this->medicament_user; }
    public function getInscriptiondateUser(): string { return $this->inscriptiondate_user; }
    public function getRoleUser(): string { return $this->role_user; }
    public function getSubscriptionUser(): string { return $this->subscription_user; }
    public function getAccountStateUser(): string { return $this->account_state_user; }
    public function getDurationUser(): string { return $this->duration_user; }

    
    public function setIdUser(int $id_user): void { $this->id_user = $id_user; }
    public function setNameUser(string $name_user): void { $this->name_user = $name_user; }
    public function setLastnameUser(string $lastname_user): void { $this->lastname_user = $lastname_user; }
    public function setEmailUser(string $email_user): void { $this->email_user = $email_user; }
    public function setPasswordUser(string $password_user): void { $this->password_user = $password_user; }
    public function setPhoneUser(string $phone_user): void { $this->phone_user = $phone_user; }
    public function setGenderUser(string $gender_user): void { $this->gender_user = $gender_user; }
    public function setBirthdayUser(string $birthday_user): void { $this->birthday_user = $birthday_user; }
    public function setHeightUser(int $height_user): void { $this->height_user = $height_user; }
    public function setWeightUser(int $weight_user): void { $this->weight_user = $weight_user; }
    public function setBmiUser(int $bmi_user): void { $this->bmi_user = $bmi_user; }
    public function setActivitylvlUser(string $activitylvl_user): void { $this->activitylvl_user = $activitylvl_user; }
    public function setIllnessUser(string $illness_user): void { $this->illness_user = $illness_user; }
    public function setAllergieUser(string $allergie_user): void { $this->allergie_user = $allergie_user; }
    public function setMedicamentUser(string $medicament_user): void { $this->medicament_user = $medicament_user; }
    public function setInscriptiondateUser(string $inscriptiondate_user): void { $this->inscriptiondate_user = $inscriptiondate_user; }
    public function setRoleUser(string $role_user): void { $this->role_user = $role_user; }
    public function setSubscriptionUser(string $subscription_user): void { $this->subscription_user = $subscription_user; }
    public function setAccountStateUser(string $account_state_user): void { $this->account_state_user = $account_state_user; }
    public function setDurationUser(string $duration_user): void { $this->duration_user = $duration_user; }
}

?>