const popup = function () {
  let body = document.body;
  let questionTogglers = document.querySelectorAll('.js-question-toggler');
  if (questionTogglers.length) {
    for (let i = 0; i < questionTogglers.length; i++) {
      questionTogglers[i].addEventListener('click', function() {
        this.parentNode.classList.toggle('question--opened');
      });
    }
  }

  let popupTrigger = document.querySelectorAll('.js-popup-trigger');
  if (popupTrigger.length) {
    let popups = document.querySelectorAll('.js-popup');
    for (let i = 0; i < popupTrigger.length; i++) {
      popupTrigger[i].addEventListener('click', function(e) {
        e.preventDefault();
        let target = this.getAttribute('data-target');
        if (popups.length) {
          Array.from(popups).forEach(function(popupEl) {
            let identifyer = popupEl.getAttribute('id');
            if (!(identifyer === target) && popupEl.classList.contains('popup--show')) {
              popupEl.classList.remove('popup--show');
              body.classList.remove('overflow');
            } else if (identifyer === target) {
              popupEl.classList.add('popup--show');
              body.classList.add('overflow');
            }
          });
        }
      });
    }
    let overlays = document.querySelectorAll('.popup__overlay');
    Array.from(overlays).forEach(function(element) {
      element.addEventListener('click', function() {
        element.parentNode.classList.remove('popup--show');
        body.classList.remove('overflow');
      });
    });
    let closeEls = document.querySelectorAll('.js-popup-close');
    Array.from(closeEls).forEach(function(element) {
      element.addEventListener('click', function() {
        let targetId = element.getAttribute('data-target');
        document.getElementById(targetId).classList.remove('popup--show');
        body.classList.remove('overflow');
      });
    });
  }
};

popup();
