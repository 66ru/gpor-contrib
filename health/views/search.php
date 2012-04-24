<div class="b-form-builder">
									<form action="/help/pharmacy/search/" method="get">
										<div class="b-form-builder_pad-20 b-form-builder_blue rc3 rc-bl rc-br">
											<fieldset class="context b-form-builder__header">
												<h3 class="context b-form-builder__title">Поиск лекарств</h3>
											</fieldset>
											<fieldset class="context horizontal">
												<dl class="form-box">
													<dt class="span-2 col-1">
														<label for="">Название</label>
													</dt>
													<dd class="span-3 col-2">
														<input type="text" value="" id="ph_search" name="ph_search" class="b-form-builder__control">
													</dd>
												</dl>
											</fieldset>
											<fieldset class="context horizontal">
												<dl class="form-box">
													<dt class="span-1 col-1">
														<label for="min">Цена от</label>
													</dt>
													<dd class="span-4 col-2">
														<input type="text" value="" id="min" name="min" class="b-form-builder__control b-form-builder__control-short">
														<label for="max">до</label>
														<input type="text" value="" id="max" name="max" class="b-form-builder__control b-form-builder__control-short">											
														<label for="">руб</label>
													</dd>													
												</dl>												
											</fieldset>
										<?php 
										if ($districts)
										{
											?>
											<fieldset class="context horizontal">
													<dl class="form-box">
														<dt class="span-1 col-1">
															<label for="">Район</label>
														</dt>
														<dd class="span-4 col-2">
															<select name="d" id="district" class="b-form-builder__control">
																<option value="">любой</option>
											<?php
											foreach ($districts as $district)
											{
												?>
															<option value="<?php echo $district['id']; ?>"><?php echo $district['name']; ?></option>
												<?php 
											}
											?>
															</select>
														</dd>													
													</dl>
											</fieldset>
											<?php
										}
										?>
										</div>
										<div class="context b-form-builder__bottom b-form-builder__bottom_blue rc3 rc-tl rc-tr">
											<input type="submit" value="Найти" class="b-form-builder__submit b-form-builder__submit-center b-form-builder__submit-find">
										</div>
									</form>										
								</div>