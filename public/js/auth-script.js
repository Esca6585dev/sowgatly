const container = document.querySelector(".container"),
      pwShowHide = document.querySelectorAll(".showHidePw"),
      pwFields = document.querySelectorAll(".password"),
      signUp = document.querySelector(".signup-link"),
      login = document.querySelector(".login-link");

    //   js code to show/hide password and change icon
    pwShowHide.forEach(eyeIcon =>{
        eyeIcon.addEventListener("click", ()=>{
            pwFields.forEach(pwField =>{
                if(pwField.type ==="password"){
                    pwField.type = "text";

                    pwShowHide.forEach(icon =>{
                        icon.classList.replace("uil-eye-slash", "uil-eye");
                    })
                }else{
                    pwField.type = "password";

                    pwShowHide.forEach(icon =>{
                        icon.classList.replace("uil-eye", "uil-eye-slash");
                    })
                }
            }) 
        })
    })

// js code to appear signup and login form
signUp.addEventListener("click", ( )=>{
    container.classList.add("active");
    document.getElementsByTagName("title")[0].innerHTML = "Agza bol | Register | Регистрация";
});
login.addEventListener("click", ( )=>{
    container.classList.remove("active");
    document.getElementsByTagName("title")[0].innerHTML = "Içeri gir | Login | Войти";
});

document.getElementById('changeLanguageLogin').addEventListener('change', function() {
    var language = this.value;
    var link = window.location.pathname;

    goLink(language, link);
});

document.getElementById('changeLanguageRegister').addEventListener('change', function() {
    var language = this.value;
    var link = window.location.pathname;

    goLink(language, link);
});

function goLink(language, link)
{
    window.location.pathname = '/' + language + link.substring(3);
}

// Add active class to the current button (highlight it)
var header = document.getElementById("btnDIV");
var btns = header.getElementsByClassName("btn");
var role = document.getElementById("role");
var companyName = document.getElementById("company_name");

for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function() {
    var current = document.getElementsByClassName("btn_active");
    
    if (current.length > 0) { 
        current[0].className = current[0].className.replace(" btn_active", "");
    }
    
    this.className += " btn_active";
    
    role.setAttribute('value', this.getAttribute('data-role-name').toLowerCase());

    if(this.innerHTML == 'Raýat') {
        companyName.classList.add('hide');
    } else {
        companyName.classList.remove('hide');
    }
  });
}