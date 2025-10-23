<?php
require 'auth.php';

deconnecterUtilisateur();

header("Location: login.php");
exit;