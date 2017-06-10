<?php

include_once("../../../classes/Constants.php");

class Test
{
	private $connection;
    function __construct($connection){
        $this->connection = $connection;
    }

    public function stringChecker($string){
        $string = trim($string);
        if ($string == "" || (strlen($string) > 255)) {
            return true; //String invalid
        } else{
            return false; //String valid
        }
    }

    public function getTestsByTeacher($id)
    {
    	$sql="  SELECT t.id,title,total_marks,date_of_test,date_of_result,type,c.name, '0' as status
                FROM egn_test as t ,egn_course as c
                WHERE user_id = '$id' AND t.course_id = c.id AND t.id NOT IN
                (SELECT t.id FROM egn_test as t ,egn_test_marks as m WHERE m.test_id = t.id)
                UNION
                SELECT t.id,title,total_marks,date_of_test,date_of_result,type,c.name, '1' as status
                FROM egn_test as t ,egn_course as c
                WHERE user_id = 1 AND t.course_id = c.id AND t.id IN
                (SELECT t.id FROM egn_test as t ,egn_test_marks as m WHERE m.test_id = t.id)
                ORDER BY date_of_test,title,type DESC";
    	$result = $this->connection->query($sql);
        if($result->num_rows > 0)
        {
            return $result;
        }
        else
        {
            return null;
        }
    }

    public function checkTestDetails($title,$total_marks,$date_of_test,$course_id,$type_id)
    {
        $type_id = trim($type_id);
        if ($this->stringChecker($title)) {
            $message = 'Test name should be less than 255';
        } elseif (!is_numeric($total_marks) || $total_marks <= 0) {
            $message = 'Enter valid marks';
        } elseif ($date_of_test < date("Y-m-d")) {
            $message = 'Please enter a valid date';
        } elseif ($course_id == NULL) {
            $message = 'Please select a course';
        } elseif ($type_id == NULL || ($type_id != "online" && $type_id != "offline")) {
            $message = 'Please select the type of test';
        } else {
            $message = Constants::STATUS_SUCCESS;
        }
        return $message;
    }

    public function getBranch(){
        $sql = "SELECT id,name FROM egn_branch ORDER BY id";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0)
        {
            return $result;
        }
        else
        {
            return null;
        }
    }
    public function getBatch($teacher_id,$branch_id){
        $sql = "SELECT DISTINCT batch.id,batch.name 
                FROM egn_batch as batch ,egn_course as c ,egn_branch as branch ,egn_teacher_course as tc ,egn_users as u 
                WHERE 
                u.role_id = " . $teacher_id . " AND 
                branch.id = " . $branch_id . " AND 
                c.branch_id=branch.id AND 
                batch.branch_id = branch.id AND
                tc.user_id = u.id AND
                tc.course_id = c.id
                ORDER BY batch.id";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0)
        {
            return $result;
        }
        else
        {
            return null;
        }
    }

    public function getCourse($teacher_id,$branch_id,$batch_id){
        $sql = "SELECT DISTINCT c.id,c.name 
                FROM egn_batch as batch ,egn_course as c ,egn_branch as branch ,egn_teacher_course as tc ,egn_users as u 
                WHERE 
                u.role_id = " . $teacher_id . " AND 
                branch.id = " . $branch_id . " AND
                batch.id = " . $batch_id . " AND
                c.branch_id=branch.id AND 
                batch.branch_id = branch.id AND
                tc.user_id = u.id AND
                tc.course_id = c.id
                ORDER BY c.id";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0)
        {
            return $result;
        }
        else
        {
            return null;
        }
    }

    public function insertTest($title,$total_marks,$date_of_test,$course_id,$teacher_id,$type_id)
    {
        $check_params =$this->checkTestDetails($title,$total_marks,$date_of_test,$course_id,$type_id);
        if($check_params == Constants::STATUS_SUCCESS){
            $sql="INSERT INTO `egn_test` (`id`, `title`, `total_marks`, `date_of_test`, `date_of_result`, `course_id`, `user_id`, `type`) 
                  SELECT * FROM (SELECT NULL as 'id', '$title', $total_marks , '$date_of_test' , NULL as 'date_of_result', $course_id, $teacher_id ,'" . (($type_id=='online')?("O"):("F")) . "') as temp
                  WHERE NOT EXISTS (
                  SELECT `id`
                  FROM `egn_test`
                  WHERE `title`='$title' AND `total_marks`='$total_marks' AND `date_of_test`='$date_of_test' AND `course_id`='$course_id' AND `user_id`='$teacher_id' AND `type`='" . (($type_id=='online')?("O"):("F")) . "');";
            $insert = $this->connection->query($sql);
            if($insert === true)
            {
                return $this->connection->insert_id;
            }
            else
            {
                return null;
            }
        }else{
            return $check_params;
        }
    }

    public function checkQuestionDetails($question,$option1,$option2,$option3,$option4,$answer,$marks){
        if($this->stringChecker($question) ||
            $this->stringChecker($option1) ||
            $this->stringChecker($option2) ||
            $this->stringChecker($option3) ||
            $this->stringChecker($option4) ||
            !is_numeric($answer) || $answer>4 || $answer<1 ||
            !is_numeric($marks) || $marks <= 0){
            return false;
        }else{
            return true;
        }
    }

    public function createQuestionQuery($question,$option1,$option2,$option3,$option4,$answer,$marks,$test_id){
        $sql = "INSERT INTO `egn_test_questions` (`id`, `question`, `option1`, `option2`, `option3`, `option4`, `answer`, `marks`, `test_id`) 
                VALUES (NULL, '$question', '$option1', '$option2', '$option3', '$option4', '$answer', '$marks', '$test_id'); ";
        return $sql;
    }

    public function insertQuestion($sql){
        $insert = $this->connection->multi_query($sql);
        if($insert === true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function deleteTest($test_id,$teacher_id){
        $sql = "DELETE FROM `egn_test` WHERE id='$test_id' AND user_id='$teacher_id';";
        $this->connection->query($sql);
        $delete = $this->connection->affected_rows;
        if($delete == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getStudentList($test_id,$teacher_id){
        $sql = "SELECT * FROM (SELECT DISTINCT s.id,s.firstname,s.lastname,t.total_marks
                FROM egn_student as s , egn_test as t ,egn_course as c ,egn_course_reg as cr ,egn_batch as batch ,egn_branch as branch 
                WHERE batch.branch_id =  branch.id AND s.batch_id = batch.id AND t.course_id = c.id AND c.branch_id = branch.id AND cr.student_id=s.id AND cr.course_id = c.id AND t.id = '$test_id' AND t.user_id='$teacher_id' AND t.type='F') as A
                LEFT OUTER JOIN
                (SELECT DISTINCT student_id,marks
                FROM egn_test_marks
                WHERE test_id = '$test_id') AS B
                ON A.id = B.student_id";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            return $result;
        }else{
            return null;
        }
    }

    public function checkMarksEntered($test_id,$action){
        $sql = "SELECT DISTINCT id
                FROM egn_test_marks
                WHERE test_id = '$test_id'";
        $result = $this->connection->query($sql);
        if($result->num_rows > 0){
            if($action == "edit"){
                return true;
            }else{
                return false;
            }
        }else{
            if($action == "add"){
                return true;
            }else{
                return false;
            }
        }
    }

    public function createInsertMarksQuery($student_id,$test_id,$marks){
        $sql = "INSERT INTO `egn_test_marks` (`id`, `student_id`, `test_id`, `marks`) 
                VALUES (NULL, '$student_id', '$test_id', '$marks');";
        return $sql;
    }

    public function insertMarks($sql){
        $insert = $this->connection->multi_query($sql);
        if($insert === true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function createUpdateMarksQuery($student_id,$test_id,$marks){
        $sql = "UPDATE `egn_test_marks`
                SET marks = '$marks'
                WHERE student_id = '$student_id' AND test_id ='$test_id';";
        return $sql;
    }

    public function updateMarks($sql){
        $update = $this->connection->multi_query($sql);
        if($update === true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    public function parentPageRedirect($message){
        header("Location: manage_test.php?message=" . $message);
    }
}
?>