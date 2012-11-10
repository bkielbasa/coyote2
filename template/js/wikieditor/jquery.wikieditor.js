/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

function Component(attributes)
{			
	this.component = 'wiki-component';		

	this.toString = function()
	{
		return this.onRender();
	};
}

function Button(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();
	
	this.component = 'wiki-button';
	
	var textarea = null;
	var self = this;
	
	this.property = 
	{
		name:				'',
		className:			'',
		openWith:			'',
		closeWith:			'',
		title:				'',
		text:				''
	};	
	this.property = $.extend(this.property, attributes); 		
	
	this.onClick = function()
	{
		textarea.insertAtCaret(self.property.openWith, self.property.closeWith, self.property.text ? self.property.text : 'foo');
	};
		
	this.onRender = function(parent)
	{
		textarea = parent;
		
		var button = $('<a title="' + this.property.title + '" class="' + this.property.className + '"><span>' + this.property.name + '</span></a>');
		button.click(this.onClick);
		
		return button;
	};
}

function Separator(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();
	
	this.component = 'wiki-separator';
	
	this.property = 
	{
		className:			''
	};	
	this.property = $.extend(this.property, attributes); 
	
	this.onRender = function()
	{
		return $('<a class="' + this.property.className + '"></a>');
	};
}

function ComboBox(attributes)
{
	this.inheritFrom = Component;
	this.inheritFrom();
	
	this.component = 'wiki-combobox';
	var textarea = null;

	this.onChange = function()
	{
		if ($(this).val() != 0)
		{
			textarea.insertAtCaret('<code=' + $(this).val() + '>', '</code>', '');
			$(this).val(0);
		}
	};
	
	this.property = 
	{
		name:				'',
		className:			'',
		openWith:			'',
		closeWith:			'',
		title:				'',
		text:				''
	};	
	this.property = $.extend(this.property, attributes);  	
		
	this.onRender = function(parent)
	{
		textarea = parent;
		comboBox = $('<select></select>').attr('name', this.property.name);
		
		$.each(this.property.items, function()
		{
			comboBox.append('<option value="' + this.id + '">' + this.value + '</option>');
		});
		comboBox.change(this.onChange);
		
		return comboBox;
	};
}


(function($)
{	
	$.fn.wikiEditor = function(componentSet)
	{
		var path;
		
		$('script').each(function(i, element)
		{
			path = element.src.match(/^(.+)\/jquery.wikieditor.js$/);

			if (path) 
			{
				$('body').append('<link rel="stylesheet" ' +
										'href="' + path[1] + '/wikieditor.css" ' +
										'type="text/css" />'
								);
				
				return false;
			}
		});
		
		if (!jQuery.isArray(componentSet))
		{
			$('body').append('<script type="text/javascript" src="' + path[1] + '/wikieditor.toolbar.js"></script>');
		}
		else
		{
			toolbarSet = componentSet;
		}

		return this.each(function()
		{
			var textarea = $(this);
			textarea.removeAttr('style');

			$(this).wrap('<div class="wiki-container"></div>');			
			var toolbar = $('<div class="wiki-toolbar"></div>').insertBefore($(this));
			$(this).wrap('<div class="wiki-editor"></div>');

			var ul = $('<ul></ul>');

			$.each(toolbarSet, function()
			{		
				var component = this;				
				var li = $('<li class="' + component.component + '"></li>');
				
				component.textarea = textarea;
				component.onRender(textarea).appendTo(li);
				li.appendTo(ul);
			});

			toolbar.append(ul);
			
			$(textarea).bind($.browser.opera ? 'keypress' : 'keydown', function(e)
			{
				if ((e.which == 9 || e.keyCode == 9) && e.shiftKey)
				{
					textarea.insertAtCaret("\t", '', "");
					
					e.preventDefault();
					return false;
				}
			});				
			
		});
	};

	$.fn.extend(
	{
		insertAtCaret: function(openWith, closeWith, value)
		{
			/**
			 * @todo Musimy uzyc tej dziwnej instrukcji (pobierajac ID textarea poprzez document.getElementById)
			 * poniewaz nie dziala prawidlowo na firefox. Do poprawy!
			 */
			if (!this.attr('id'))
			{
				this.attr('id', 'textarea_content');
			}
			var element = document.getElementById(this.attr('id'));

			if (document.selection) 
			{
				element.focus();
				sel = document.selection.createRange();
				sel.text = openWith + (sel.text.length > 0 ? sel.text : value) + closeWith;

				element.focus();
			}
			else if (element.selectionStart || element.selectionStart == '0') 
			{
				var startPos = element.selectionStart;
				var endPos = element.selectionEnd;
				var scrollTop = element.scrollTop;

				if (startPos != endPos)
				{
					var value = openWith + element.value.substring(startPos, endPos) + closeWith;
				}
				else
				{
					var value = openWith + value + closeWith;
				}

				element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);

				element.focus();
				element.selectionStart = startPos + value.length;
				element.selectionEnd = startPos + value.length;
				element.scrollTop = scrollTop;
			}
			else 
			{
				element.value += (openWith + value + closeWith);
				element.focus();
			}
		}
	});
}
)(jQuery);