<?php
include_once __DIR__ . '/../../Model/config.php';
include_once __DIR__ . '/../../Model/SUPPORT_MODULE/ReclamationMessage.php';

class ReclamationMessage_Controller
{
    public function add_message(ReclamationMessage $msg): int
    {
        $db  = config::getConnexion();
        $sql = 'INSERT INTO reclamation_message (id_reclam, id_user, body)
                VALUES (:id_reclam, :id_user, :body)';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_reclam' => $msg->getIdReclam(),
                'id_user'   => $msg->getIdUser(),
                'body'      => $msg->getBody(),
            ]);
            return (int) $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Reclamation message insert failed: ' . $e->getMessage());
        }
    }

    /**
     * Stored replies only (oldest first). The ticket description is appended in build_timeline().
     */
    public function get_messages(int $id_reclam): array
    {
        $db  = config::getConnexion();
        $sql = 'SELECT m.*, u.name_user AS author_name
                FROM reclamation_message m
                LEFT JOIN user u ON u.id_user = m.id_user
                WHERE m.id_reclam = :id
                ORDER BY m.sent_at ASC, m.id_message ASC';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id_reclam]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function get_messages_after(int $id_reclam, int $after_id): array
    {
        $db  = config::getConnexion();
        $sql = 'SELECT m.*, u.name_user AS author_name
                FROM reclamation_message m
                LEFT JOIN user u ON u.id_user = m.id_user
                WHERE m.id_reclam = :id AND m.id_message > :after
                ORDER BY m.id_message ASC';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id_reclam, 'after' => $after_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function get_message_by_id(int $id_message): ?array
    {
        $db  = config::getConnexion();
        $sql = 'SELECT m.*, u.name_user AS author_name
                FROM reclamation_message m
                LEFT JOIN user u ON u.id_user = m.id_user
                WHERE m.id_message = :id
                LIMIT 1';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id_message]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function to_timeline_item(array $message): array
    {
        $sentAt = trim((string) ($message['sent_at'] ?? ''));
        return [
            'id'         => (int) ($message['id_message'] ?? 0),
            'body'       => trim((string) ($message['body'] ?? '')),
            'idUser'     => (int) ($message['id_user'] ?? 0),
            'authorName' => trim((string) ($message['author_name'] ?? '')),
            'sentAt'     => $sentAt,
            'sentLabel'  => self::format_sent_label($sentAt),
            'isInitial'  => false,
        ];
    }

    /**
     * Full conversation: virtual first message from description_reclam + stored replies.
     */
    public function build_timeline(array $reclamation, array $dbMessages, string $ownerName = ''): array
    {
        $ownerId     = (int) ($reclamation['id_user'] ?? 0);
        $description = trim((string) ($reclamation['description_reclam'] ?? ''));
        $openingDate = trim((string) ($reclamation['dateouvert_reclam'] ?? ''));
        $timeline    = [];

        if ($description !== '') {
            $timeline[] = [
                'id'         => 0,
                'body'       => $description,
                'idUser'     => $ownerId,
                'authorName' => $ownerName !== '' ? $ownerName : 'Client',
                'sentAt'     => $openingDate,
                'sentLabel'  => self::format_sent_label($openingDate),
                'isInitial'  => true,
            ];
        }

        foreach ($dbMessages as $message) {
            $timeline[] = $this->to_timeline_item($message);
        }

        return $timeline;
    }

    public static function format_sent_label(string $sentAt): string
    {
        if ($sentAt === '' || strtotime($sentAt) === false) {
            return '';
        }
        return date('M j, Y H:i', strtotime($sentAt));
    }
}
