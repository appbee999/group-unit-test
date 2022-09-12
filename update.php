<?php
  include('connect.php');
  $id=$_GET['id'];
  $email=$_POST['email'];
  $password=$_POST['password'];
  mysqli_query($connect,"Update tblusers set email='$email', password='$password' where id='$id'");
  header('location:index.php');
?>
