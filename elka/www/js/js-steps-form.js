/*
Форма с пошаговым заполнением
 */
js_steps_form = function(opts){

            var defaults = {
                'containerClass' : '', // контейнер формы
                'stepClass' : '', // класс контейнера шага формы
                'activeStepClass' : '', // класс контейнера текущего шага
                'buttonClass' : '', // класс контейнера сабмита
                'activeButtonClass' : '', // класс контейнера сабмита текущего шага
                'rowValueClass' : 'b-form-box', // класс контейнера со строкой формы
                'rowErrorClass' : 'b-form-box__error', // класс ошибки для контейнера
                'stepIdPrefix' : 'forms__step_id_',
                'stepButtonIdPrefix' : 'forms__button_id_',
                'errorTextSelector' : '.b-user-error-message p',
                'errorContainerClass' : 'b-form-builder_hint',
                'ajax' : true
            };

            var o = $.extend({}, defaults, opts);
            var obj = false;
            var steps = false;
            var formObj = false;
            var buttons = false;
	    
	    var curStep = false;
	    var activeStep = false;
	    
            if (o.containerClass)
                var obj = $("."+o.containerClass);
            if (!obj)
                obj = $(document);


            var initUi = function (evt) {
            }
            
            var gotoNextStep = function (data) {
          data = data ? data : {};
	      steps.hide();
	      buttons.hide();
	      if (curStep === false) {
		activeStep = obj.find("."+o.activeStepClass);
		activeStep.show();
		curStep = steps.index(activeStep);
		
		obj.find("."+o.activeButtonClass).show();
	      }
	      else if (curStep < (steps.length - 1 )) {
		buttons.eq(curStep).removeClass("."+o.activeButtonClass);
		steps.eq(curStep).removeClass("."+o.activeButtonClass);
		curStep++;
		buttons.eq(curStep).addClass("."+o.activeButtonClass).show();
		steps.eq(curStep).addClass("."+o.activeButtonClass).show();
	      }
	      else {
        var successText = data['text'] ? data['text'] : 'Спасибо, форма принята.';
		formObj.html("<div>"+successText+"</div>");
	      }
		
	    }
	    
	    var putErrors = function (errors) {
	      for (i in errors) {
                var errorInput = obj.find('[name="'+i+'"]').eq(0);
                var errorRow = errorInput.closest("."+o.rowValueClass);
                errorRow.find("."+o.errorContainerClass).stop().animate({"left" : "10px", "opacity" : "1"}, 200);
                errorRow.addClass(o.rowErrorClass);
                errorRow.find(o.errorTextSelector).html(errors[i]);
            }

	    }
	    
	    var removeErrors = function () {
            obj.find("."+o.errorContainerClass).stop().animate({"left" : "0px", "opacity" : "0"}, 200, function(){
                obj.find("."+o.rowErrorClass).removeClass(o.rowErrorClass);
            });
	    }

            // start
            this.init = function () {
                steps = obj.find("."+o.stepClass);
                buttons = obj.find("."+o.buttonClass);
                obj.find("."+o.errorContainerClass).css({"opacity" : "0"});
		        gotoNextStep();

                formObj = obj.find("form");
		
                formObj.ajaxForm({
                        'data': {},
                        'dataType' : 'json',
                        'beforeSubmit': function(a,f,o) {
			            removeErrors();
                        },
                        'success': function(data) {
                            if (data['success']){
			      gotoNextStep(data);
			    }
			    else if (data['errors']){
                  setTimeout(function(){
                      putErrors(data['errors']);
                  }, 200);
			    }
                        }
                    });
            }

            this.initUi = function () {
                initUi();
            }
        };

