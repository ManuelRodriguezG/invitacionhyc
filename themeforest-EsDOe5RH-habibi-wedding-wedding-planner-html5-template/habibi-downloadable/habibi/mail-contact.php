<?php

	$to = "wpoceanmarketing@gmail.com"; // this is your Email address
	$from  = $_POST['email']; // this is the sender's Email address
	$sender_name = $_POST['name'];
	$adress = $_POST['adress'];
	$date = $_POST['date'];
	$guest = $_POST['guest'];
	$meal = $_POST['meal'];
	$service = $_POST['service'];
	$note = $_POST['note'];

	$subject = "Form submission";

	$message = $sender_name . " has send the contact message. His / Her wedding date is " . $date . " and his / her adress is "  . $adress . " and his / her guest number is " . $guest . " and his / her Service is " . $service . " and his / her Meal Name is " . $meal . ". He / she wrote the following... ". "\n\n" . $note;

	$headers = 'From: ' . $from;
	mail($to, $subject, $message, $headers);

?>