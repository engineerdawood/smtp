var Vop = {
    csrfToken: vop_token,

    join: function (btn, action){
        var form = $(btn).closest('form');
        form.attr({'action': action}).submit();
    },

    alert: function (msg, type){
        type = type || 'info';
        alert(msg);
    },

    show: function (box){
        $(box).slideDown();
    },

    hide: function (box){
        $(box).slideUp();
    },

    ajax: function(obj){
        obj = $.merge(obj, {
            method: 'GET',
            url: '',
            dataType: 'json',
            data: {},
            success: function(){},
            error: function(){}
        });

        if(obj.url.indexOf(base_url) == -1){
            obj.url = base_url + obj.url;
        }

        return $.ajax({
            type: obj.method,
            url: obj.url,
            data: obj.data,
            dataType: obj.dataType,
            cache: false,
            headers: {'X-CSRF-TOKEN': Vop.csrfToken},
            success: obj.success,
            error: obj.error
        });
    },


};

$.fn.extend({
    animateCss: function (animationName, callback) {
        return this.each(function(){
            callback = callback || '';
            var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
            this.addClass('animated ' + animationName).one(animationEnd, function() {
                $(this).removeClass('animated ' + animationName);
                if(typeof callback == 'function')
                    callback();
            });
            return this;
        });
    },

    addLoader: function(){
        return this.each(function(){
            var target = $(this);
            if(target.find('.loading-box').length > 0){
                return;
            }
            var loader = $('<div class="loading-box">').append(
                $('<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>')
            );
            target.addClass('loading').append(loader);
            return this;
        });
    },

    removeLoader: function(){
        return this.each(function(){
            var target = $(this);
            target.removeClass('loading');
            target.find('.loading-box').remove();
            return this;
        });
    },

    makeSelect: function(){
        return this.each(function(){
            $(this).find('li a').click(function(){
                var el = $(this);
                var name = el.closest('ul.dropdown-menu').data('name');
                var cont = el.parents('.input-group-btn');
                var selText = $.trim($(this).text());
                var icon = cont.find('.dropdown-toggle > span').get(0).outerHTML;
                cont.find('.dropdown-toggle').html(selText + ' ' + icon);
                cont.find('[name="' + name + '"]').val(el.data('value'));
            });

            if(typeof $(this).data('selectedval') == 'string' && $(this).data('selectedval').length > 0){
                $(this).find('a[data-value="'+$(this).data('selectedval')+'"]').click();
            }

            return this;
        });
    },

});


String.prototype.alertMessage = function(type, time) {
    time = time || 4000;
    // TODO
    console.log('TODO - ' + type);
    var popup = jQuery('.bb-alert');
    var text = this.toString();
    var popClass = icon = '';
    switch(type) {
        case "error":
            popClass = 'alert-danger';
            icon = 'times-circle';
            break;
        case "warning":
            popClass = 'alert-warning';
            icon = 'exclamation-triangle';
            break;
        case "success":
            popClass = 'alert-success';
            icon = 'check';
            break;
        default:
            popClass = 'alert-info';
            icon = 'info-circle';
            break;
    }
    popup.html('<i class="fa fa-' + icon + '"> &nbsp;' + text).addClass(popClass).fadeIn();
    setTimeout(function(){
        popup.html('').removeClass(popClass).fadeOut();
    }, time);
};