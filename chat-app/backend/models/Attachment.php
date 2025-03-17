<?php
class Attachment extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'attachments');
        if (!$this->checkTable()) {
            $this->handleError("Attachments table not found");
        }
    }

    public function create($messageId, $fileName, $fileType, $fileSize, $fileUrl) {
        $sql = "INSERT INTO attachments (message_id, file_name, file_type, file_size, file_url) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issis", $messageId, $fileName, $fileType, $fileSize, $fileUrl);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    public function getByMessageId($messageId) {
        $sql = "SELECT * FROM attachments WHERE message_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
