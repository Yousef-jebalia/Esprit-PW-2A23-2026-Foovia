<?php

include_once(__DIR__ . '/../Model/config.php');
include_once(__DIR__ . '/../Model/user.php');

class Controller_user {

    
    public function add_user(User $user) {
        $birthday = trim($user->getBirthdayUser());
        $birthday = $birthday !== '' ? $birthday : null;
        $inscriptionDate = trim($user->getInscriptiondateUser());
        $inscriptionDate = $inscriptionDate !== '' ? date('Y-m-d', strtotime($inscriptionDate)) : date('Y-m-d');

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
            duration_user,
            login_count_user
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
            :duration,
            :login_count
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
                'birthday'  => $birthday,
                'height'    => $user->getHeightUser(),
                'weight'    => $user->getWeightUser(),
                'bmi'       => $user->getBmiUser(),
                'activity'  => $user->getActivitylvlUser(),
                'illness'   => $user->getIllnessUser(),
                'allergie'  => $user->getAllergieUser(),
                'medicament'=> $user->getMedicamentUser(),
                'date'      => $inscriptionDate,
                'role'      => $user->getRoleUser(),
                'subscription' => $user->getSubscriptionUser(),
                'account_state' => $user->getAccountStateUser(),
                'duration' => $user->getDurationUser(),
                'login_count' => 0
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

    public function release_expired_bans(): int {

        $sql = "UPDATE user
                SET account_state_user = 'active',
                    duration_user = '00:00:00',
                    ban_until_user = NULL,
                    failed_attempts_user = 0
                WHERE account_state_user = 'banned'
                  AND ban_until_user IS NOT NULL
                  AND ban_until_user <= NOW()";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->rowCount();
        } catch (Exception $e) {
            error_log('Error releasing expired bans: ' . $e->getMessage());
            return 0;
        }
    }

    public function increment_user_login_count(int $userId): bool {

        $sql = "UPDATE user
                SET login_count_user = COALESCE(login_count_user, 0) + 1
                WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            return $query->execute(['id' => $userId]);
        } catch (Exception $e) {
            error_log('Error incrementing login count: ' . $e->getMessage());
            return false;
        }
    }

    public function get_user_login_statistics(int $userId): ?array {

        $sql = "SELECT id_user, name_user, email_user, COALESCE(login_count_user, 0) AS login_count_user
                FROM user
                WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $userId]);
            $result = $query->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            error_log('Error reading login statistics: ' . $e->getMessage());
            return null;
        }
    }

    public function get_top_logged_users(int $limit = 10, string $role = ''): array {

        $sql = "SELECT id_user, name_user, email_user, role_user, COALESCE(login_count_user, 0) AS login_count_user
                FROM user";

        $params = [];
        if ($role !== '') {
            $sql .= " WHERE role_user = :role";
            $params['role'] = $role;
        }

        $sql .= " ORDER BY login_count_user DESC, id_user ASC LIMIT :limit";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);

            if ($role !== '') {
                $query->bindValue(':role', $params['role'], PDO::PARAM_STR);
            }

            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Error reading top logged users: ' . $e->getMessage());
            return [];
        }
    }

    public function get_login_role_distribution(): array {

        $sql = "SELECT role_user,
                       COUNT(*) AS users_count,
                       SUM(COALESCE(login_count_user, 0)) AS total_logins
                FROM user
                GROUP BY role_user
                ORDER BY total_logins DESC, role_user ASC";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Error reading role login distribution: ' . $e->getMessage());
            return [];
        }
    }

    public function get_available_roles(): array {

        $sql = "SELECT DISTINCT role_user
                FROM user
                WHERE role_user IS NOT NULL AND role_user <> ''
                ORDER BY role_user ASC";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            $rows = $query->fetchAll();
            $roles = [];

            foreach ($rows as $row) {
                $roles[] = (string) ($row['role_user'] ?? '');
            }

            return $roles;
        } catch (Exception $e) {
            error_log('Error reading available roles: ' . $e->getMessage());
            return [];
        }
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

    public function filter_users_by_gender(string $gender, string $term = '') {

        $sql = "SELECT * FROM user
                WHERE gender_user = :gender";

        if ($term !== '') {
            $sql .= " AND (
                        CAST(id_user AS CHAR) LIKE :term
                     OR name_user LIKE :term
                     OR lastname_user LIKE :term
                     OR email_user LIKE :term
                     OR phone_user LIKE :term
                     OR role_user LIKE :term
                    )";
        }

        $sql .= " ORDER BY id_user DESC";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);

            $params = ['gender' => $gender];
            if ($term !== '') {
                $params['term'] = '%' . $term . '%';
            }

            $query->execute($params);
            return $query;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function process_ban_countdown(int $userId): array {

        $this->release_expired_bans();

        $sql = "SELECT id_user, account_state_user, duration_user, ban_until_user
                FROM user
                WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $userId]);
            $user = $query->fetch();

            if (!$user || ($user['account_state_user'] ?? 'active') !== 'banned') {
                return [
                    'is_banned' => false,
                    'remaining' => '00:00:00'
                ];
            }

            $banUntil = $user['ban_until_user'] ?? null;
            if (!$banUntil) {
                return [
                    'is_banned' => true,
                    'remaining' => (string) ($user['duration_user'] ?? '01:00:00')
                ];
            }

            $remainingSeconds = strtotime($banUntil) - time();

            if ($remainingSeconds <= 0) {
                $releaseSql = "UPDATE user
                               SET account_state_user = 'active',
                                   duration_user = '00:00:00',
                                   ban_until_user = NULL,
                                   failed_attempts_user = 0
                               WHERE id_user = :id";
                $releaseQuery = $db->prepare($releaseSql);
                $releaseQuery->execute(['id' => $userId]);

                return [
                    'is_banned' => false,
                    'remaining' => '00:00:00'
                ];
            }

            $hours = floor($remainingSeconds / 3600);
            $minutes = floor(($remainingSeconds % 3600) / 60);
            $seconds = $remainingSeconds % 60;
            $remaining = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $updateSql = "UPDATE user SET duration_user = :duration WHERE id_user = :id";
            $updateQuery = $db->prepare($updateSql);
            $updateQuery->execute([
                'duration' => $remaining,
                'id' => $userId
            ]);

            return [
                'is_banned' => true,
                'remaining' => $remaining
            ];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [
                'is_banned' => false,
                'remaining' => '00:00:00'
            ];
        }
    }

    public function register_failed_login_attempt(int $userId): array {

        $db = config::getConnexion();

        try {
            $selectSql = "SELECT failed_attempts_user FROM user WHERE id_user = :id";
            $selectQuery = $db->prepare($selectSql);
            $selectQuery->execute(['id' => $userId]);
            $current = $selectQuery->fetch();

            $attempts = (int) ($current['failed_attempts_user'] ?? 0) + 1;

            if ($attempts >= 3) {
                $banUntil = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                $banSql = "UPDATE user
                           SET failed_attempts_user = 0,
                               account_state_user = 'banned',
                               duration_user = '00:05:00',
                               ban_until_user = :ban_until
                           WHERE id_user = :id";
                $banQuery = $db->prepare($banSql);
                $banQuery->execute([
                    'ban_until' => $banUntil,
                    'id' => $userId
                ]);

                return [
                    'is_banned' => true,
                    'remaining_attempts' => 0,
                    'remaining' => '00:05:00'
                ];
            }

            $updateSql = "UPDATE user SET failed_attempts_user = :attempts WHERE id_user = :id";
            $updateQuery = $db->prepare($updateSql);
            $updateQuery->execute([
                'attempts' => $attempts,
                'id' => $userId
            ]);

            return [
                'is_banned' => false,
                'remaining_attempts' => 3 - $attempts,
                'remaining' => '00:00:00'
            ];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [
                'is_banned' => false,
                'remaining_attempts' => 0,
                'remaining' => '00:00:00'
            ];
        }
    }

    public function reset_failed_login_attempts(int $userId): void {

        $sql = "UPDATE user
                SET failed_attempts_user = 0,
                    account_state_user = 'active',
                    ban_until_user = NULL,
                    duration_user = '00:00:00'
                WHERE id_user = :id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $userId]);
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
        $birthday = trim($user->getBirthdayUser());
        $birthday = $birthday !== '' ? $birthday : null;
        $inscriptionDate = trim($user->getInscriptiondateUser());
        $inscriptionDate = $inscriptionDate !== '' ? date('Y-m-d', strtotime($inscriptionDate)) : date('Y-m-d');

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
                'birthday'   => $birthday,
                'height'     => $user->getHeightUser(),
                'weight'     => $user->getWeightUser(),
                'bmi'        => $user->getBmiUser(),
                'activity'   => $user->getActivitylvlUser(),
                'illness'    => $user->getIllnessUser(),
                'allergie'   => $user->getAllergieUser(),
                'medicament' => $user->getMedicamentUser(),
                'date'       => $inscriptionDate,
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

    public function get_user_by_email(string $email) {
        $sql = "SELECT id_user, name_user, email_user, password_user, account_state_user, duration_user, ban_until_user, role_user FROM user WHERE LOWER(email_user) = :email";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['email' => strtolower(trim($email))]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log('Error getting user by email: ' . $e->getMessage());
            return null;
        }
    }

    public function set_user_reset_token(int $id_user, string $token, string $expires_at): bool {
        $sql = "UPDATE user SET reset_token = :token, reset_token_expires_at = :expires_at WHERE id_user = :id_user";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'token' => $token,
                'expires_at' => $expires_at,
                'id_user' => $id_user
            ]);
        } catch (Exception $e) {
            error_log('Error setting reset token: ' . $e->getMessage());
            return false;
        }
    }

    public function get_user_by_token(string $token) {
        $sql = "SELECT id_user, name_user, email_user, reset_token_expires_at FROM user WHERE reset_token = :token";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['token' => $token]);
            return $query->fetch();
        } catch (Exception $e) {
            error_log('Error getting user by token: ' . $e->getMessage());
            return null;
        }
    }

    public function update_user_password_by_token(int $id_user, string $password): bool {
        $sql = "UPDATE user SET password_user = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id_user = :id_user";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'password' => $password,
                'id_user' => $id_user
            ]);
        } catch (Exception $e) {
            error_log('Error updating password by token: ' . $e->getMessage());
            return false;
        }
    }

    public function update_user_subscription(int $id_user, string $subscription): bool {
        $sql = "UPDATE user SET subscription_user = :subscription WHERE id_user = :id_user";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'subscription' => $subscription,
                'id_user' => $id_user
            ]);
        } catch (Exception $e) {
            error_log('Error updating user subscription: ' . $e->getMessage());
            return false;
        }
    }

}  

?>
