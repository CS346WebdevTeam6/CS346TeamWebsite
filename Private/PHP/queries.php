<?php

  /*
  // Returns an array containing arrays which have the indexs 'Username' and 'Score'
  // which contain their respective values
  */
  function get_scores($questionId) {
    global $db;

    try {
      $query = "SELECT Scores.UserId, Scores.Score, Students.FirstName, Students.LastName
                FROM Scores
                INNER JOIN Students ON Scores.UserId = Students.StudentId
                WHERE QuestionId=?
                ORDER By Students.LastName;";
      $stmt = $db->prepare($query);
      $stmt->execute([$questionId]);
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    } catch (PDOException $e) {
        db_disconnect();
        exit("Aborting: There was a database error when retrieving " .
             "the score.");
    }
}

function get_student_by_username($username) {
  global $db;

  try{
    $query = "SELECT *
              FROM Students
              WHERE Username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute(["username" => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the student.");
  }
}


function add_score($questionId, $studentId, $score, $studentAnswer) {
  global $db;

  try{
    $query = "INSERT INTO Scores (QuestionId, UserId, Score, StudentAnswer)
              VALUES (:questionId, :userId, :score, :studentAnswer)
              ";
    $stmt = $db->prepare($query);
    $stmt->execute(["questionId" => $questionId, "userId" => $studentId, "score" => $score, "studentAnswer" => $studentAnswer]);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error inserting the score to the database.");
  }
}

function add_correct_submission($questionId) {
	global $db;

  try{
    $query = "UPDATE Questions
              SET CorrectSubmissions = CorrectSubmissions + 1
			  WHERE QuestionId = :questionId
              ";
    $stmt = $db->prepare($query);
    $stmt->execute([":questionId" => $questionId]);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error incrementing the correct submissions.");
  }
}

function get_active() {
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE Status = 3";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of active questions.");
  }
}

function get_question($questionId) {
  global $db;

  try {
    $query = "SELECT *
              FROM Questions
              WHERE QuestionId = :questionId";
    $stmt = $db->prepare($query);
    $stmt->execute([":questionId" => $questionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: There was a database error when retrieving " .
           "the questions.");
  }
}

function get_question_answers($questionId) {
  global $db;

  try {
    $query = "SELECT AnswerText, Correct, ShortAnswer FROM Answers
              WHERE QuestionId = :questionId";
    $stmt = $db->prepare($query);
    $stmt->execute(["questionId" => $questionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: There was a database error when retrieving " .
           "the questions answers.");
  }
}

/*
// returns an array which should have the average score stored at index 'Score' UNTESTED
*/
function update_average($questionId) {
  global $db;

  try {
    $query = "UPDATE Questions
              SET ClassAverage =
              (SELECT AVG(Score)
              FROM Scores
              WHERE QuestionId = :questionId)
              WHERE QuestionId = :questionId";
    $stmt = $db->prepare($query);
    $stmt->execute([":questionId" => $questionId]);
    return  true;
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: There was a database error when updating " .
           "the class average for the question.");
  }
}

function get_average($id){
  global $db;

  try{
    $query = "SELECT ClassAverage
              FROM Questions
              WHERE QuestionId = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of active questions.");
  }
}

function deactivate_all($time) {
  global $db;

    try {
      $query = "UPDATE Questions
                SET Status = 4, ActivationEnd = :endTime
                WHERE Status = 3";
      $stmt = $db->prepare($query);
      $stmt->execute([":endTime" => $time]);
      return true;
    } catch (PDOException $e) {
        db_disconnect();
        exit("Aborting: There was a database error when deactivating " .
             "the questions.");
    }
}

//tested & works on webdev server
function add_question($id, $status, $type, $text, $points, $section, $description) {
  global $db;

  try {
    $query = "INSERT INTO Questions(QuestionId, Status, QuestionType,
                                     QuestionText, PointsAvailable, Section,
                                     Description)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$id, $status, $type, $text, $points, $section, $description]);
    return true;
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: there was a database error when inserting a new " .
            "question. in function Add_question");
  }
}

function update_question($id, $status, $type, $text, $points, $section, $description) {
  global $db;

  try {
    $query = "UPDATE Questions
              SET Status =:status, QuestionType =:type,
                  QuestionText =:qtext, PointsAvailable =:points, Section =:section,
                  Description =:description
              WHERE QuestionId =:qId";
    $stmt = $db->prepare($query);
    $stmt->execute([":qId" => $id, ":status"=>$status, ":type"=>$type, ":qtext"=>$text,
                    ":points"=>$points, ":section"=>$section, ":description"=>$description]);
    return true;
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: there was a database error when updating the " .
            "question.");
  }
}

function activate_question($questionId, $statusId, $activate_start) { // can be used to set to draft or activate as well as deactivate a single question
  global $db;

    try {
      $query = "UPDATE Questions
                SET Status = :status, ActivationStart =:start
                WHERE QuestionId = :questionId";
      $stmt = $db->prepare($query);
      $stmt->execute([":questionId" => $questionId, ":status" => $statusId,
                      ":start" => $activate_start ]);
      return  true;
    } catch (PDOException $e) {
        db_disconnect();
        exit("Aborting: There was a database error when changing " .
             "the question status.");
    }
}

function deactivate_question($questionId, $statusId, $time) { // can be used to set to draft or activate as well as deactivate a single question
  global $db;

    try {
      $query = "UPDATE Questions
                SET Status = :status, ActivationEnd = :endTime
                WHERE QuestionId = :questionId";
      $stmt = $db->prepare($query);
      $stmt->execute([":questionId" => $questionId, ":status" => $statusId,
                      ":endTime" => $time ]);
      return  true;
    } catch (PDOException $e) {
        db_disconnect();
        exit("Aborting: There was a database error when changing " .
             "the question status.");
    }
}

function get_active_question($id) {
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE Status = 3 AND QuestionId = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of active questions.");
  }
}


function check_id($id){
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE QuestionId = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($result){ //If there is a question already, output a message to the instructor
      echo "There was an error inserting your question into the database.".
      " A question with the id $id already exists. Please try a different id.";
      return true;
    }
  } catch (PDOException $e){
      db_disconnect();
      exit("Aborting: there was a database error when checking the database for " .
            "the question ID.");
  }
}


function insert_keywords($id, $keyword){
  global $db;

  try {
    $query = "INSERT INTO Keywords
              VALUES (?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$id, $keyword]);
    return true;
  } catch (PDOException $e) {
    db_disconnect();
    exit("Aborting: there was a database error when inserting a new " .
          "question.");
  }
}

function view_question($id) {
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE QuestionId = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the requested question.");
  }
}

function add_answer($id, $text, $correct, $number) {
  global $db;

  try{
    $query = "INSERT INTO Answers(QuestionId, AnswerText, Correct, NumberCorrect)
              VALUES(?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$id, $text, $correct, $number]);
    return true;
  }
  catch (PDOException $e){
    db_disconnect();
    exit("Aborting: there was a database error when inserting a new question");
  }
}

function add_short($id, $text){
  global $db;

  try{
    $query = "INSERT INTO Answers(QuestionId, ShortAnswer)
              VALUES(?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$id, $text]);
    return true;
  }
  catch (PDOException $e){
    db_disconnect();
    exit("Aborting: there was a database error when inserting a new question");
  }
}

function edit_question($id) { //to retrieve question information for editing
  global $db;

  try {
    $query = "SELECT *
              FROM Questions
              WHERE QuestionId = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: There was an error when retrieving the question.");
  }
}

function delete_question($id) {
  global $db;

  try{
    $query = "DELETE FROM Questions
              WHERE QuestionId=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    echo "Question $id successfully deleted from database.";
    return true;
  } catch (PDOException $e) {
      db_disconnect();
      exit("Aborting: There was an error when deleting the question. " .
        "Please try again later.");
  }
}

function delete_keywords($id){
  global $db;

  try{
    $query = "DELETE FROM Keywords
              WHERE QuestionId=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
  }
  catch (PDOException $e){
    db_disconnect();
    exit("Aborting: There was an error when deleting the question. " .
      "Please try again later.");
  }
}

function delete_answers($id){
  global $db;

  try{
    $query = "DELETE FROM Answers
              WHERE QuestionId=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
  }
  catch (PDOException $e){
    db_disconnect();
    exit("Aborting: There was an error when deleting the question. " .
      "Please try again later.");
  }
}

function get_question_list() { //function to populate all the questions the instructor has in the database
  global $db;

  try{
    $query = "SELECT *
              FROM Questions ORDER BY QuestionId";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions available to edit.");
  }
}


function get_keyword_list($id) {
  global $db;

  try{
    $query = "SELECT Keyword
              FROM Keywords WHERE QuestionId = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions available to edit.");
  }
}

function get_student_answers($id) {
  global $db;

  try{
    $query = "SELECT StudentAnswer, Score
              FROM Scores WHERE QuestionId = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions available to edit.");
  }
}

function get_answer_choices($id){
  global $db;
  try{
    $query = "SELECT AnswerText, Correct, ShortAnswer
              FROM Answers WHERE QuestionId = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("Aborting: There was an error when retrieving the question.");
  }
}

//function to get all deactivated questions for instructor to view stats
function get_deactivated_question_list() {
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE Status=4 ORDER BY QuestionId";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions available to edit.");
  }
}

//function to get all complete questions for instructor to view stats
function get_completed_question_list() {
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE Status=2 OR Status=4
              ORDER BY QuestionId";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions available to edit.");
  }
}

function search_keyword($keyword){
  global $db;

  try{
    $query = "SELECT QuestionId
              FROM Keywords WHERE Keyword = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$keyword]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the keyword.");
  }
}

function search_section($section){
  global $db;

  try{
    $query = "SELECT QuestionId
              FROM Questions WHERE Section = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$section]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the section.");
  }
}

function search_points_available($points){
  global $db;

  try{
    $query = "SELECT QuestionId
              FROM Questions WHERE PointsAvailable = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$points]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the points available.");
  }
}

function search_score($score){
  global $db;

  try{
    $query = "SELECT QuestionId
              FROM Scores WHERE Score = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$score]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the score");
  }
}

function student_search_score($qid, $id){
  global $db;

  try{
    $query = "SELECT Score
              FROM Scores WHERE QuestionId = :qid
              AND UserId = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([":qid"=>$qid, ":id"=>$id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the score");
  }
}

function search_student_answers($qid, $sid){
  global $db;

  try{
    $query = "SELECT StudentAnswer
              FROM Scores WHERE QuestionId = :qid
              AND UserId = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([":qid"=>$qid, ":id"=>$sid]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the score");
  }
}

function search_questions($id){
  global $db;

  try{
    $query = "SELECT *
              FROM Questions
              WHERE QuestionId = ?
              AND Status = 4";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of questions matching the score");
  }
}

function display_PAV_table() { //function to populate all the scores in the database.
  global $db;

  try{
    $query = "SELECT DISTINCT PointsAvailable FROM Questions
              ORDER BY PointsAvailable";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of PointsAvailable.");
  }
}

function display_SEC_table() { //function to populate all the scores in the database.
  global $db;

  try{
    $query = "SELECT DISTINCT Section FROM Questions
              ORDER BY Section";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of sections.");
  }
}

function display_S_table() { //function to populate all the scores in the database.
  global $db;

  try{
    $query = "SELECT DISTINCT Score FROM Scores
              ORDER BY Score";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of scores.");
  }
}

function display_K_table() { //function to populate all the keywords in the database
  global $db;

  try{
    $query = "SELECT DISTINCT Keyword FROM Keywords
              ORDER BY Keyword";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchall(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    db_disconnect();
    exit("There was an error fetching the list of keywords available to edit.");
  }
}


function change_password($id , $role , $oldPass , $newPass1 , $newPass2)
{
	if($newPass1 == $newPass2 && strpos($newPass1 , $UN) == false)
	{
		$newPass = $newPass1 ;
	}
	else
	{
		echo "Your new password does not meet the standards." ;
		header('changePassword.php') ;
	}

	if($role === "student")
	{
		try
		{
			$query = "SELECT Salt FROM Students WHERE StudentId = :id" ;
			$stmt = $db->prepare($query) ;
			$stmt->execute(["id" => $id]) ;
			$stmt = $stmt->fetch(PDO::FETCH_ASSOC) ;
			$salt = $stmt['Salt'] ;
			$query = "SELECT PasswordChanges FROM Students WHERE StudentId = :id" ;
			$stmt = $db->prepare($query) ;
			$stmt->execute(["id" => $id]) ;
			$stmt = $stmt->fetch(PDO::FETCH_ASSOC) ;
			$pwc = $stmt['PasswordChanges'] + 1 ;
			$oldPass = hash_password($oldPass , $salt) ;
			$newPass = hash_password($newPass , $salt) ;
			$query = "UPDATE Students SET HashPassword = :newPass AND PasswordChanges = :pwc WHERE StudentId = :id AND HashPassword = :oldPass" ;
			$stmt = $db->prepare($query) ;
			$stmt->execute(["newPass" => $newPass , "pwc" => $pwc , "id" => $id , "oldPass" => $oldPass]) ;
		}
		catch (PDOException $e)
		{
			echo "Error updating password" ;
		}
	}
	else
	{
		try
		{
			$query = "SELECT Salt FROM Instructors WHERE InstructorId = :id" ;
			$stmt = $db->prepare($query) ;
			$stmt->execute(["id" => $id]) ;
			$stmt = $stmt->fetch(PDO::FETCH_ASSOC) ;
			$salt = $stmt['Salt'] ;
			$query = "SELECT PasswordChanges FROM Instructors WHERE InstructorId = :id" ;
			$stmt = $db->prepare($query) ;
			$stmt->execute(["id" => $id]) ;
			$stmt = $stmt->fetch(PDO::FETCH_ASSOC) ;
			$pwc = $stmt['PasswordChanges'] + 1 ;
			$oldPass = hash_password($oldPass , $salt) ;
			$newPass = hash_password($newPass , $salt) ;
			$query = "UPDATE Instructors SET HashPassword = :newPass AND PasswordChanges = :pwc WHERE InstructorId = :id AND HashPassword = :oldPass" ;
			$stmt = $db->prepare($query) ;
			$stmt->execute(["newPass" => $newPass , "pwc" => $pwc , "id" => $id , "oldPass" => $oldPass]) ;
		}
		catch (Exception $e)
		{
			echo "Error updating password" ;
		}
	}
}

function hash_password($password , $salt)
{
	$iv = mcrypt_create_iv(22,MCRYPT_DEV_URANDOM) ;
	$encoded_iv = str_replace('+' , '.' , base64_encode($iv)) ;
	$salt = $salt . $encoded_iv . '$' ;
	$hashed_password = crypt($password , $salt) ;
	return $hashed_password ;
}


?>
