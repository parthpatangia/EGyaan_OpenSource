<?php

require_once("../../../classes/Constants.php");

class Course
{
    private $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function getCourse($teacherStatus, $userId, $multiQuery, $batchId, $courseId, $batchName)
    {
        if ($teacherStatus == "yes" && $userId > 0 && $multiQuery == 'no' && $batchId == 0 && $courseId == 0 && $batchName == null) { //This will give course details for Teacher
            $sql = "SELECT eBranch.id AS branchId,eBranch.name AS branchName,eBatch.id AS batchId,eBatch.name 
AS batchName,eBatch.branch_id AS batchBranchId,eCourse.id AS courseId,eCourse.name AS courseName,eCourse.batch_id 
AS courseBatchId,eTeacherCourse.id AS teacherCourseId,eTeacherCourse.user_id 
AS teacherCourseUserId,eTeacherCourse.course_id AS teacherCourseCourseId,eTeacherCourse.addedby_user_id 
AS teacherCourseAddedbyUserId FROM `egn_teacher_course` AS eTeacherCourse,`egn_course` AS eCourse,`egn_batch` 
AS eBatch,`egn_branch` AS eBranch WHERE eTeacherCourse.course_id = eCourse.id AND eCourse.batch_id = eBatch.id 
AND eBatch.branch_id = eBranch.id AND eTeacherCourse.user_id = '$userId'";
        } elseif ($teacherStatus == 'no' && $userId == 0 && $multiQuery == 'no' && $batchId == 0 && $courseId > 0 && $batchName == null) { //This will give course details in General
            $sql = "SELECT * FROM `egn_course` WHERE id = '$courseId'";
        } elseif ($teacherStatus == "no" && $userId > 0 && $multiQuery == 'no' && $batchId == 0 && $courseId == 0 && $batchName == null) { //This will give course details for Student
            $sql = "SELECT * FROM `egn_course_reg` AS cr, `egn_course` AS c WHERE cr.course_id = c.id 
AND student_id='$userId'";
        } elseif ($teacherStatus == "no" && $userId == 0 && $multiQuery == 'yes' && $batchId == 0 && $courseId == 0 && $batchName == null) {
            $sql = "SELECT eBranch.id AS branchId,eBranch.name AS branchName,eBatch.id AS batchId,eBatch.name 
AS batchName,eBatch.branch_id AS batchBranchId,eCourse.id AS courseId,eCourse.name AS courseName,eCourse.batch_id 
AS courseBatchId FROM `egn_course` AS eCourse,`egn_batch` AS eBatch,`egn_branch` AS eBranch 
WHERE eCourse.batch_id = eBatch.id AND eBatch.branch_id = eBranch.id";
        } elseif ($teacherStatus == "no" && $userId == 0 && $multiQuery == 'yes' && $batchId > 0 && $courseId == 0 && $batchName == null) {
            $sql = "SELECT eBranch.id AS branchId,eBranch.name AS branchName,eBatch.id AS batchId,eBatch.name 
AS batchName,eBatch.branch_id AS batchBranchId,eCourse.id AS courseId,eCourse.name AS courseName,eCourse.batch_id 
AS courseBatchId FROM `egn_course` AS eCourse,`egn_batch` AS eBatch,`egn_branch` AS eBranch 
WHERE eCourse.batch_id = eBatch.id AND eBatch.branch_id = eBranch.id AND eCourse.batch_id = '$batchId'";
        } elseif ($teacherStatus == "no" && $userId == 0 && $multiQuery == 'yes' && $batchId == 0 && $courseId == 0 && $batchName != null) {
            $sql = "SELECT eBranch.id AS branchId,eBranch.name AS branchName,eBatch.id AS batchId,eBatch.name 
AS batchName,eBatch.branch_id AS batchBranchId,eCourse.id AS courseId,eCourse.name AS courseName,eCourse.batch_id 
AS courseBatchId FROM `egn_course` AS eCourse,`egn_batch` AS eBatch,`egn_branch` AS eBranch 
WHERE eCourse.batch_id = eBatch.id AND eBatch.branch_id = eBranch.id AND eBatch.name = '$batchName'";
        } else {
            $sql = "SELECT * FROM `egn_course`";
        }
        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            return $result;
        } else {
            return false;
        }
    }

// In case of multiple inserts, you need to check whether or not each insert query is being executed, if it is executed only then execute the next query, or else if a particular query is not executed, first delete all the previous RELATED INSERT queries and then return false.
    public function insertCourse($name, $batch_id)
    {
        $name = $this->connection->real_escape_string($name);
        $batch_id = $this->connection->real_escape_string($batch_id);

        $sql = "SELECT * FROM `egn_course` WHERE name='$name' AND batch_id='$batch_id'";
        $result = $this->connection->query($sql);

        if ($result->num_rows == 0) {
            $insert_sql = "INSERT INTO `egn_course`(`name`,`batch_id`) VALUES ('$name','$batch_id')";
            $insert = $this->connection->query($insert_sql);
            if ($insert === true) {
                return true;
            } else {
                return false;
            }
        } else {
            $message = Constants::STATUS_EXISTS;
            return $message;
        }
    }

    public function updateCourse($id, $name, $batch_id)
    {
        $name = $this->connection->real_escape_string($name);
        $batch_id = $this->connection->real_escape_string($batch_id);

        $sql = "UPDATE `egn_course` SET `name`='$name' WHERE id='$id' AND `batch_id`='$batch_id'";
        $update = $this->connection->query($sql);

        if ($update === true) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteCourse($id)
    {
        $sql = "DELETE FROM egn_course WHERE id='$id'";
        $delete = $this->connection->query($sql);

        if ($delete === true) {
            return true;
        } else {
            return false;
        }
    }
}

?>