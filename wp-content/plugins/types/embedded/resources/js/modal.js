(function($){

    $.fn.types_modal_box = function(prop){

        // Default parameters

        var options = $.extend({
            height : 364,
            width : 525
        },prop);

        return this.submit(function(e){
            if ( $(this).hasClass('js-types-do-not-show-modal')) {
                return;
            }
            add_block_page();
            pop_up = add_popup_box();
            add_styles(pop_up);
            $.colorbox({
                html: pop_up.html(),
                fixed: true,
                closeButton: false
            });
        });

        function add_styles(pop_up){
            $('.types_modal_box', pop_up).css({
                background: "#fff none no-repeat 0 0",
                border: "1px solid #888",
                boxShadow: "7px 7px 20px 0px rgba(50, 50, 50, 0.75)",
            });

            $('.types_modal_box .message', pop_up).css({
                color: "#f05a28",
                fontFamily: "'Open Sans', Helvetica, Arial, sans-serif",
                fontSize: "25px",
                padding: "0 10px",
                textAlign: "center"
            });
            $('.types_modal_box .message span', pop_up).css({
                background: "transparent url("+types_modal.spinner+") no-repeat 0 50%",
                paddingLeft: "30px",
                lineHeight: "105px"
            });
        }

        function add_block_page(){
            var block_page = $('<div class="types_block_page"></div>');
            $(block_page).appendTo('body');
        }

        function add_popup_box(){
            var marginLeft, height, paddingTop, width;
            var header = types_modal.header;

            if ( !header ) {
                return;
            }

            var html = '<div class="types_block_page">';
            html += '<div class="types_modal_box '+types_modal.class+'">';
            html += '<div class="message"><span>'+types_modal.message+'</span></div>';
            if ( 'endabled' == types_modal.state ) {
                html += '<div class="header"><div>';
                if ( types_modal.question ) {
                    html += '<span class="question">';
                    html += types_modal.question;
                    html += '</span>';
                }
                html += '<p>'+header+'</p></div></div>';
            } else {
                options.height = 106;
            }
            html += '</div>';
            html += '</div>';

            var pop_up = $(html);

            $('.header', pop_up).css({
                height: "259px",
                textAlign: "center",
                color: "#fff",
                fontSize:"15px",
                backgroundImage: 'url('+types_modal.image+'?v=2)',
                backgroundRepeat: "no-repeat",
            });
            /**
             * header div
             */
            marginLeft = "290px";
            width = "220px";
            paddingTop = "50px";
            height = "150px";
            switch(types_modal.class) {
                case 'cred':
                    paddingTop = "77px";
                    marginLeft = "260px";
                    width = "250px";
                    height = "100px";
                    break;
                case 'access':
                    marginLeft = "270px";
                    width = "250px";
                    paddingTop = "25px";
                    height = "120px";
                    break;
            }
            $('.header div', pop_up).css({
                float: "left",
                height: height,
                marginLeft: marginLeft,
                paddingTop: paddingTop,
                textAlign: "left",
                width: width

            });
            /**
             * header p
             */
            $('.header p', pop_up).css({
                fontFamily: "'Open Sans', Helvetica, Arial, sans-serif",
                fontSize: "18px",
                lineHeight: "1.2em",
                margin: 0
            });
            $('.header .question', pop_up).css({
                display: "block",
                fontSize: "14px",
                marginBottom: "5px"
            });
            return pop_up;
        }

        return this;
    };

})(jQuery);
