<?php

//Demarrer la session si elle n'est pas deja démarrée
if (session_status() === PHP_SESSION_NONE){
    session_start();
}

//Verifier si l'user est connecté 
function estConnecte(){
    return isset($_SESSION['user_id']);
}

//recup l'user connecté
function getUtilisateur(){

    //est ce que l'user est connecté?
    if(!estConnecte()){
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'nom' => $_SESSION['user_nom'],
        'prenom' => $_SESSION['user_prenom'],
        'role' => $_SESSION['user_role'],
    ];
}

//verifier si l'user est vendeur
function estVendeur(){
    return estConnecte() && $_SESSION['user_role'] === 'vendeur';
}

//verifier si l'user est client
function estClient(){
    return estConnecte() && $_SESSION['user_role'] === 'client';
}

//rediriger si non connecté
function requireAuth(){
     if(!estConnecte()){
        header("Location: login.php");
        exit;
    }
}

//rediriger si non vendeur
function requireVendeur(){
    requireAuth();
    if (!estVendeur()){
        header("Location: index.php");
        exit;
    }
}

//connecter un utilisateur 
function connecterUtilisateur($user)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_role'] = $user['role'];
}

//Deconnecter un utilisateur 
function deconnecterUtilisateur(){
    session_unset();
    session_destroy();
}



