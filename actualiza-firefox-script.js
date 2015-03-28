var actualiza=localStorage.getItem("actualiza-firefox");
if(!actualiza){
	localStorage.setItem("actualiza-firefox","on");
}	  

function apagarActualiza(){
	localStorage.setItem("actualiza-firefox","off");
}

window.onload = function(){
    if(localStorage["actualiza-firefox"]=="on"){
        var alerta = document.getElementById("af_actualiza_firefox");
        alerta.setAttribute('style','visibility: visible !important;');
    }
}