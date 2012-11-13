<link href="/css/b-forms.css" rel="stylesheet"  type="text/css" />
<script src="/js/jquery.form.js"  type="text/javascript"></script>
<script src="/js/jquery.maskedinput-1.3.js"  type="text/javascript"></script>
<script src="/js/jquery.url.js"  type="text/javascript"></script>
<script src="/js/app.js"  type="text/javascript"></script>
<script src="/js/js-steps-form.js"  type="text/javascript"></script>

<div class="b-separator b-separator_size_5"></div>
<a name="form"></a>
<h2 class="b-header b-header_size_h2 b-header_margin_left-20">Стать участником акции</h2>

<div class="b-container b-container_color_green">
    <div class="b-container__inner">
        <div class="b-separator b-separator_size_10"></div>

        <?php
        echo $cForm->render();
        ?>
            <script type="text/javascript">
            jQuery(function($) {
                $.mask.definitions['~']='[+-]';
                $('#ElkaJoinForm_phone').mask('+7 (999) 999-99-99');
            });
            </script>
            <script type="text/javascript">
                app.module.register( 'joinForm', js_steps_form, {
                        'containerClass' : 'js-formyform_ElkaJoinForm',
                        'stepClass' : 'forms__step',
                        'activeStepClass' : 'forms__step_state_active',
                        'buttonClass' : 'forms__button',
                        'activeButtonClass' : 'forms__button_state_active',
                        'ajax' : true
                    });
            </script>

        <div style="clear: both;"></div>
        <div class="b-separator b-separator_size_20"></div>
    </div>


</div>
