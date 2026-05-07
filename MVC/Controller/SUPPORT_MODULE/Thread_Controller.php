<?php
include_once __DIR__ . '/../../Model/config.php';
include_once __DIR__ . '/../../Model/SUPPORT_MODULE/Thread.php';
include_once __DIR__ . '/../../Model/SUPPORT_MODULE/ThreadMessage.php';

class Thread_Controller
{
    // ------------------------------------------------------------------ threads

    /**
     * Create a new thread. Returns the new auto-increment id.
     */
    public function create_thread(Thread $thread): int
    {
        $db  = config::getConnexion();
        $sql = 'INSERT INTO thread (id_reclam, title, description, published_by)
                VALUES (:id_reclam, :title, :description, :published_by)';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_reclam'    => $thread->getIdReclam(),
                'title'        => $thread->getTitle(),
                'description'  => $thread->getDescription(),
                'published_by' => $thread->getPublishedBy(),
            ]);
            return (int) $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Thread insert failed: ' . $e->getMessage());
        }
    }

    /**
     * Return all threads ordered newest-first.
     */
    public function get_threads(): array
    {
        $db  = config::getConnexion();
        $sql = 'SELECT t.*, 
                       (SELECT COUNT(*) FROM thread_message m WHERE m.id_thread = t.id_thread) AS reply_count
                FROM thread t
                ORDER BY t.published_at DESC';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Return one page of threads (newest-first). $page is 1-based.
     */
    public function get_threads_paged(int $page, int $per_page = 8): array
    {
        $db     = config::getConnexion();
        $offset = ($page - 1) * $per_page;
        $sql    = 'SELECT t.*,
                          (SELECT COUNT(*) FROM thread_message m WHERE m.id_thread = t.id_thread) AS reply_count
                   FROM thread t
                   ORDER BY t.published_at DESC
                   LIMIT :limit OFFSET :offset';
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Total thread count (for pagination math).
     */
    public function count_threads(): int
    {
        $db = config::getConnexion();
        try {
            return (int) $db->query('SELECT COUNT(*) FROM thread')->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Fetch a single thread row by id (returns array or null).
     */
    public function get_thread_by_id(int $id): ?array
    {
        $db  = config::getConnexion();
        $sql = 'SELECT * FROM thread WHERE id_thread = :id LIMIT 1';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Hard-delete a thread (cascade removes its messages via FK ON DELETE CASCADE).
     */
    public function delete_thread(int $id): bool
    {
        $db  = config::getConnexion();
        $sql = 'DELETE FROM thread WHERE id_thread = :id';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception('Thread delete failed: ' . $e->getMessage());
        }
    }

    // --------------------------------------------------------------- messages

    /**
     * Insert a reply. Returns the new message id.
     */
    public function add_message(ThreadMessage $msg): int
    {
        $db  = config::getConnexion();
        $sql = 'INSERT INTO thread_message (id_thread, id_user, body)
                VALUES (:id_thread, :id_user, :body)';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_thread' => $msg->getIdThread(),
                'id_user'   => $msg->getIdUser(),
                'body'      => $msg->getBody(),
            ]);
            return (int) $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Message insert failed: ' . $e->getMessage());
        }
    }

    /**
     * Return all messages for a thread, oldest-first.
     */
    public function get_messages(int $thread_id): array
    {
        $db  = config::getConnexion();
        $sql = 'SELECT * FROM thread_message WHERE id_thread = :id ORDER BY sent_at ASC';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $thread_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Hard-delete one message.
     */
    public function delete_message(int $id): bool
    {
        $db  = config::getConnexion();
        $sql = 'DELETE FROM thread_message WHERE id_message = :id';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception('Message delete failed: ' . $e->getMessage());
        }
    }
}
?>
