(function($) {

$.widget('ui.sympalSlot', {
  
  options : {
    edit_mode : 'popup'
  },
  
  _init : function()
  {
    this.initialize();
  },
  
  openEditor : function()
  {
    var self = this;
    
    // disable the non-edit-mode controls
    this.disableNonEditControls();
    
    // make sure the edit button is hidden
    $('.sympal_slot_button', this.element).hide();
    
    // determine the editor object
    if (this._getData('edit_mode') == 'inline')
    {
      var editor = $('.sympal_slot_content', self.element);
    }
    else
    {
      var editor = $('#fancybox-inner');
    }
    this._setData('editor', editor);
    
    // register the ajaxSuccess function on the editor
    editor.bind('ajaxResponseSuccess', function() {
      editor.trigger('block');
      
      // setup the ajax form submit
      $('form:not(.sympal_ajax_submit)', editor).submit(function() {
        editor.trigger('block');
        
        $(this).ajaxSubmit( {
          error: function(xhr, textStatus, errorThrown)
          {
            editor.trigger('unblock');
            // display some sort of error
          },
          success: function(responseText, statusText, xhr)
          {
            $('.form_body', editor).html(responseText);
            editor.trigger('ajaxResponseSuccess');
            editor.trigger('unblock');
          }
        });
        
        return false;
      }); // end ajax for submit
      
      // make sure the submit event doesn't get registered twice
      $(this).addClass('sympal_ajax_submit');
      
      // initialize any slot-specific functionality if it exists
      var formClass = 'sfSympalSlot'+self._getData('slot_type');
      if ($.isFunction(self.element[formClass]))
      {
        self.element[formClass](self);
      }
      
      // Keep track of the currently focused editor
      $('input:text, textarea', $(this)).focus(function() {
        currentlyFocusedSympalEditor = $(this);
      });
      
      // hook up the cancel button
      $('form input.cancel', this).click(function() {
        self.closeEditor();
        
        return false;
      });
      
      editor.trigger('unblock');
    }); // end ajaxResponseSuccess
    
    // bind the block and unblocks
    editor.bind('block', function() {      
      // inline, we've gotta block the whole page
      if ($(this).css('display') == 'block')
      {
        // you actually want to block the parent, #facebox-wrapper
        $(this).parent().block();
      }
      else
      {
        $.blockUI();
      }
    });
    editor.bind('unblock', function() {
      if ($(this).css('display') == 'block')
      {
        $(this).parent().unblock();
      }
      else
      {
        $.unblockUI();
      }
    });
    
    // actually ajax in the data and trigger editor.ajaxResponseSuccess
    var href = $('.sympal_slot_button', this.element).attr('href');
    if (this._getData('edit_mode') == 'inline')
    {
      editor.load(href, function() {
        editor.trigger('ajaxResponseSuccess');
      });
    }
    else
    {
      $.fancybox(href, {
        'zoomSpeedIn': 300,
        'zoomSpeedOut': 300,
        'overlayShow': true,
        'height': 440,
        'autoDimensions': false,
        'hideOnContentClick': false,
        'type': 'ajax',
        'onComplete': function() {
          editor.trigger('ajaxResponseSuccess');
        },
        'onCleanup': function() {
          self.closeEditor();
        },
      });
    }
    
  },
  
  closeEditor : function()
  {
    self = this;
    
    if (!this.getEditor())
    {
      return;
    }
    
    // unbind the ajax event from the editor
    this.getEditor().unbind('ajaxResponseSuccess');
    this._setData('editor', null);
    
    $.blockUI();
    
    // ajax in the content, and then set things back up
    $('.sympal_slot_content', self.element).load(
      self._getData('view_url'),
      function() {
        // reinitialize the non-edit-mode controls
        self.enableNonEditControls();
        
        // make sure fancybox is closed
        $.fancybox.close();
        
        $.unblockUI();
      }
    );
  },
  
  initialize: function()
  {
    var self = this;
    
    // register non-edit-handlers: effects for when the slot is not being edited
    nonEditHandlers = {};
    
    // enable editing on double-click
    nonEditHandlers['dblclick'] = function()
    {
      self.openEditor()
    }
    nonEditHandlers['mouseover'] = function()
    {
      $('.sympal_slot_button', self.element).show();
    }
    nonEditHandlers['mouseout'] = function()
    {
      $('.sympal_slot_button', self.element).hide();
    }
    this._setData('nonEditHandlers', nonEditHandlers);
    
    // attach the nonEditHandler events
    this.enableNonEditControls();
    
    // enable editing if the slot button is clicked
    $('a.sympal_slot_button', this.element).click(function() {
      self.openEditor()
      return false;
    });
    
    // highlight editable area on edit button hover
    $('a.sympal_slot_button', this.element).hover(function() {
      $('.sympal_slot_content', self.element)
        .css('opacity', .2)
        .children().css('opacity', .2);
    }, function() {
      $('.sympal_slot_content', self.element)
        .css('opacity', 1)
        .children().css('opacity', 1);
    });
  },
  
  enableNonEditControls : function()
  {
    self = this;
    
    // bind all of the non-edit-mode handlers
    $.each(this._getData('nonEditHandlers'), function(key, value) {
      self.element.bind(key, value);
    });
  },
  
  disableNonEditControls : function()
  {
    self = this;
    
    // disable all of the non-edit-mode handlers
    $.each(this._getData('nonEditHandlers'), function(key, value) {
      self.element.unbind(key, value);
    });
  },
  
  getEditor : function()
  {
    return this._getData('editor');
  }
  
});

})(jQuery);



jQuery(document).ready(function(){    
  
  $('.sympal_slot_wrapper').each(function() {
    $(this).sympalSlot({
      edit_mode: $(this).metadata().edit_mode,
      slot_type: $(this).metadata().type,
      view_url:  $(this).metadata().view_url,
    });
  });
  

  $('.sympal_inline_edit_bar_edit_buttons').show();
  
  // globally save slots on the "save" edit button
  $('#inline-edit-bar-buttons-menu .sympal_save_content_slots').click(function(){
    $('form.sympal_slot_form').submit();
  });
  
  // globally hide slot forms on the "cancel" edit button
  $('#inline-edit-bar-buttons-menu .sympal_disable_edit_mode').click(function(){
    $('form.sympal_slot_form input.cancel').click();
  });
});