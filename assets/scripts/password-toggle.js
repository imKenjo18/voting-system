function checkPasswordValue(inputElementSelector, spanElementSelector) {
  let input = document.querySelector(inputElementSelector);

  if (input.value != "") {
    document.querySelector(spanElementSelector).style.display = "flex";
  } else {
    document.querySelector(spanElementSelector).style.display = "none";
  }
}

function togglePasswordView(inputElementSelector, imgElementSelector) {
  let isPasswordShown;
  let input = document.querySelector(inputElementSelector);
  
  if (input.type == "password") {
    input.type = "text";
    isPasswordShown = true;
  } else if (input.type == "text") {
    input.type = "password";
    isPasswordShown = false;
  }

  let icon = document.querySelector(imgElementSelector);

  if (isPasswordShown == true) {
    icon.src = "assets/icons/eye-off.svg";
    icon.title = "Hide password";
  } else if (isPasswordShown == false) {
    icon.src = "assets/icons/eye.svg";
    icon.title = "Show password";
  }
}