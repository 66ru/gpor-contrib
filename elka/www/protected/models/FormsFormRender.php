<?php
/**
 * Модель формопостроителя
 * @author stepanoff
 * @version 1.0
 * todo: вынести верстку в шаблоны. Класс ошики изначально не рендерится
 */
class FormsFormRender extends CForm
{
    public $formInputLayout = '
    <fieldset class="b-form-row">
        <div class="b-form-row__col1">{label}</div>
        <div class="b-form-row__col2"><div class="context"><div class="b-form-box">{input}{error}{hint}</div></div></div>
    </fieldset>';
    public $formErrorLayout = '<div class="b-form-builder_container-for-hint">
											<div class="relative b-form-builder_hint ">
												<div class="b-form-builder_hint__ugol-left-top"></div>
												<div class="b-form-builder_hint__frame">
													<div class="b-form-builder-blc-cn b-form-builder-blc-tl"></div>
													<div class="b-form-builder-blc-cn b-form-builder-blc-tr"></div>
													<div class="b-form-builder_hint__content">
														<div class="b-form-builder_hint__left-line"></div>
														<div class="b-form-builder_hint__right-line"></div>
														<div class="b-user-error-message">
															<p>{error}</p>
														</div>
													</div>
													<div class="b-form-builder-blc-cn b-form-builder-blc-bl"></div>
													<div class="b-form-builder-blc-cn b-form-builder-blc-br"></div>
												</div>
											</div>
										</div>';
    public $stepJs = false;
    public $defaultClasses = array(
        'text'=>'grid-span-7',
        'password'=>'grid-span-7',
        'textarea'=>'grid-span-9',
        'file'=>'grid-span-7',
        'listbox'=>'grid-span-7',
        'dropdownlist'=>'grid-span-7',
    );
    public $renderSafeAttributes = false;
    public $startPageIndex = false;

    protected $output = '';

    public function init() {
    }

    public function render()
    {
        if ($this->startPageIndex !== false) {
            $i = 0;
            foreach ($this->model->getStepsStructure() as $page_id => $pageElements) {
                if ($i == $this->startPageIndex) {
                    $this->model->activePageId = $page_id;
                    break;
                }
                $i++;
            }
        }
        $this->configure($this->model->getFormRenderData());

        $oldReq = CHtml::$afterRequiredLabel;
        CHtml::$afterRequiredLabel = '&nbsp;<span class="b-form__label__star">*</span>';
        $this->output = '<div class="b-from js-form'.$this->getUniqueId().' col-1 span-3">';
        $this->output .=  $this->renderBegin();

        $this->output .= $this->renderFormElements();

        $this->output .=  $this->renderButtons();
        $this->output .=  $this->renderEnd();
        if ($this->stepJs)
            $this->output .= $this->renderStepsJs();
        $this->output .= '</div>';
        CHtml::$afterRequiredLabel = $oldReq;
        return $this->output;
    }

    public function renderStepsJs () {
        $output = '<script type="text/javascript">';
        $output .= "
app.module.register( 'jsForm".$this->getUniqueId()."', js_steps_form, {
        'containerClass' : 'js-form".$this->getUniqueId()."',
        'stepClass' : 'forms__step',
        'activeStepClass' : 'forms__step_state_active',

        'buttonClass' : 'forms__button',
        'activeButtonClass' : 'forms__button_state_active',
        'ajax' : true

    });
        ";
        $output .= '</script>';
        return $output;
    }

    public function renderFormElements ()
    {
        $output = '';
        $errors = $this->model->getErrors();
        if($this->title)
            $output .= '<h3>'.$this->title.'</h3><hr/>';

        foreach ($this->getElements() as $k=>$element)
        {
            if (get_class($element)=='CFormStringElement')
            {
                $element->layout = $this->formInputLayout;
                $output .= $element->render();
            }
            elseif (get_class($element)!='CFormInputElement')
            {
                if ($element->model)
                    $this->renderFormElements($element);
            }
            else
            {
                $error = false;
                if(!empty($errors[$element->name]))
                {
                    $error = $errors[$element->name][0];
                }
                if ($this->renderSafeAttributes && !$this->form->model->isAttributeSafe($k))
                    continue;

                if(isset($this->defaultClasses[$element->type]))
                {
                    // if we have default value
                    if(!array_key_exists('class',$element->attributes))
                    {
                        // but we have no attribute class defined
                        $element->attributes['class'] = $this->defaultClasses[$element->type]; // default will be set
                    }
                    elseif(is_array($element->attributes['class']))
                    {
                        // but if array defined
                        foreach ($element->attributes['class'] as $key => $param)
                        {
                            // every key
                            if(!strpos($param,'grid') && isset($this->defaultClasses[$element->type][$key])) //will be checked
                                $element->attributes['class'][$key] = $this->defaultClasses[$element->type][$key]; // and applied if exists not yet
                        }
                    }
                    elseif (!@strpos($element->attributes['class'],'grid'))
                    {
                    }
                }
                $elementLayout = $this->formInputLayout;
                $errorLayout = $this->formErrorLayout;
                $elementLayout = preg_replace('|{error}|',$errorLayout,$elementLayout);
                if ($error)
                    $elementLayout = preg_replace('|{error}|',$error,$elementLayout);
                $element->layout = $elementLayout;
                $elementOutput = $element->render();
                if ($error) {
                    $elementOutput = preg_replace('|\"b-form-box\"|','"b-form-box b-form-box__error"',$elementOutput);
                }
                $output .= $elementOutput;

                if(!empty($element->attributes['description']))
                    $output .= '<div class="forms__hint">' . $element->attributes['description'].'</div>';


            }
        }
        return $output;
    }

    public function renderButtons() {
        $output='';
        foreach($this->getButtons() as $button)
            $output.=$this->renderButton($button);
        return $output!=='' ? '<div class="b-separator b-separator_size_20"></div><fieldset class="b-form-row">
        <div class="b-form-row__col2"><div class="_submit-line">'.$output.'</div></div></fieldset>' : '';
    }

    public function renderButton($element) {
        $attrs = $element->attributes;
        $class = isset($attrs['class']) ? $attrs['class'] : '';
        $attrs['class'] = 'b-btn__submit';
        $element->attributes = $attrs;

        $label = $element->label;
        $element->label = '';
        $output='
                            <div class="b-btn b-btn_color_green b-btn_size_big b-btn_text-size_big '.$class.'">
								<ins class="b-btn__crn-left"></ins>
								<ins class="b-btn__crn-right"></ins>
								'.$element->render().'
								<span class="b-btn__text">'.$label.'</span>
							</div>
        ';
        return $output;
    }
}
?>