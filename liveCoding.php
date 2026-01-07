<?php 
require_once "Database.php";
require_once "Models/Photo.php";

class PhotoRepository {

    $pdo = Database::getInstance();

    public function saveWithTags (Photo $photo, array $tagsList) {
        
        try {
        
            
            $pdo->beginTransaction();
            
            $sql = $pdo->prepare("INSERT INTO photos (`id`, `title`, `description`, `imageLink`, `fileSize`, `dimensions`, `state`, `viewCount`, `publishedAt`, `createdAt`, `updatedAt`, `userId`, `albumId`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sql->execute([$photo->id, $photo->title, $photo->description, $photo->imageLink, $photo->fileSize, $photo->dimensions, $photo->state, $photo->viewCount, $photo->publishedAt, $photo->createdAt, $photo->updatedAt, $photo->albumId, $photo->albumId])



            // foreach ($tagsList as $tag) {
            //     $result = isInDataBase($tag);
            //     if (!$result) {
            //         $sql = $pdo->prepare("INSERT INTO `tags` (`id`, `slug`, `photoCount`) VALUES (?, ?, ?)");
            //         $sql->execute([$tag->id, $tag->slug, $tag->photoCount]);
            //     }
            // }




            if (count($tagsList) <= 10 || count($tagsList) > 0) {
                foreach ($tagsList as $tag) {
                    $result = isInDataBase($tag);
                    if (!$result) {
                        $sql = $pdo->prepare("INSERT INTO `tags` (`id`, `slug`, `photoCount`) VALUES (?, ?, ?)");
                        $sql->execute([$tag->id, $tag->slug, $tag->photoCount]);
                    }
                    else {
                        $currentDate = new date('yyyy-mm-dd-hh-mm-ss');
                        $sql = $pdo->prepare("INSERT INTO `photo_tags` (`photoId`, `tagId`, `createdAt`) VALUES (?, ?, ?)");
                        $sql->execute([$photo->id, $tag->slug, $currentDate]);
                    }
                }

            }



            $pdo->commit();
        }
        catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollback()
            }
        }
    }


    private function isInDataBase(Tag $tag) {
        $sql = "SELECT * FROM tags WHERE id = ?";

        $stmt=$pdo->prepare($sql);
        $stmt->execute([$tags->id]);

        return $stmt->fetch()

    }
}





?>