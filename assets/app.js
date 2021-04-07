/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

 // loads the jquery package from node_modules
//  var $ = require('jquery');
import $ from 'jquery';
// start the Stimulus application
import 'bootstrap';
 // import the function from greet.js (the .js extension is optional)
 // ./ (or ../) means to look for a local file
// var greet = require('./greet');
// import greet from './greet';

$(document).ready(function() {
//affichage des produits
    $('.Plus').hover (
        function(){
            $(this).children('.PlusB').show();
            $(this).css({'opacity': 0.7});
        },
        function(){
            $(this).children('.PlusB').hide();
            $(this).css({'opacity': 0.5});
        }
    );
//insertion du nom image form creation/ajour
    $(".custom-file input").on('change', function(e){
        let nom = e.currentTarget.files[0].name;
        $(this).next().text(nom);
    });
    //detail produit depuis panier
    $(".retourP").click(function(){
        $(this).next().removeClass();
    });
});

