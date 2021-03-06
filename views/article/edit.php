	<div class="btn-group" id="editorConstrols">
		<div class="btn" onclick="_editor.controls.header_accept(event,this);">h4</div>
		<div class="btn" onclick="_editor.controls.bold_accept(event,this);"><b>B</b></div>
		<div class="btn" onclick="_editor.controls.italic_accept(event,this);"><i>I</i></div>
		<div class="btn" onclick="_editor.controls.format_accept(event,this);">Eliminar formato</div>
		<div class="btn dropdown-toggle" onclick="_editor.controls.link_open(event,this);">Enlace
			<div class="dropdown-menu padded">
				<textarea class="hidden">{%articleOB_articleLinksJSON%}</textarea>
				<h4>Crear enlace</h4>
				<p>Insertar un enlace sobre el texto seleccionado.</p>
				<div><input type="text" name="linkHref"/></div>
				<div class="btn-group right"><div class="btn close"><i class="icon-remove-sign"></i> Cancelar</div><div class="btn" onclick="_editor.controls.link_accept(event);"><i class="icon-ok-sign"></i> Aceptar</div></div>
			</div>
		</div>
		{%edit.paragraph%}
	</div>
	<div class="writer articleNode">
		<div id="canvasControls" class="canvasControls"></div>
		<div id="ranges"></div>
		<article id="canvas" class="canvas content" contenteditable="true" onblur="_editor.signals.blur(event);" onmousedown="_editor.signals.mousedown(event);" onmouseup="_editor.signals.mouseup(event);">{%articleOB_articleText%}</article>
	</div>
