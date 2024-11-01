jQuery(document).ready( function($){


    /**
     * ajax show posll processing
     */
    var ajax_executed = 0;
    var is_visible_pop = 0;
    console.log( $('.ajax_showpoll_marker').length );
    if( $('.ajax_showpoll_marker').length > 0 ){
        setInterval( function(){
     
            if( ajax_executed == 0 ){

                var data = {
                    security : wvp_local_data.nonce,
                    action : 'verify_shop_vote_popup'
                }

                jQuery.ajax({url: wvp_local_data.ajaxurl,
                    type: 'POST',
                    data: data,            
                    beforeSend: function(msg){
                        ajax_executed = 1;
                        },
                        success: function(msg){
                            console.log( msg );
                            var obj = jQuery.parseJSON( msg );
                            if( obj.result == 'success' ){
                                if( obj.status == 'on' ){
                                    ajax_executed = 0;
                                    if( is_visible_pop == 0 ){
                                        is_visible_pop = 1;
                                        $('.show_vote_popup_link').click();
                                    }
                                    
                                }
                                if( obj.status == 'off' ){
                                    ajax_executed = 0;
                                    if( is_visible_pop == 1 ){
                                        is_visible_pop = 0;
                                        $.fancybox.getInstance().close();
                                    }
                                }
                            } 
                            
                        } , 
                        error:  function(msg) {
                            console.log( msg );		
                        }          
                });
            }
        }, 5000 );
    }


    /**
     * reg form submit serialize
     */
     $( "#registration_form" ).on( "submit", function( event ) {
       
        console.log( $( this ).serialize() );

        SetCookie( 'registration_form', $( this ).serialize(), 1 );
      });


    /**
     * reg form required scroll
     */

     var delay = 0;
     var offset = 150;
     
     document.addEventListener('invalid', function(e) {
        $(e.target).addClass("invalid");
        $('html, body').animate({
           scrollTop: $($(".invalid")[0]).offset().top - offset
        }, delay);
     }, true);
     document.addEventListener('change', function(e) {
        $(e.target).removeClass("invalid")
     }, true);

    // assign proxy to user front ned forms
    $('#assign_proxies_to_external').change(function(){
        if( $(this).val() == 'yes' ){
            $('.for_external').fadeIn();
            $('.not_external').fadeOut();
        }
        if( $(this).val() == 'no' ){
            $('.for_external').fadeOut();
            $('.not_external').fadeIn();
        }
    });

    // assign proxy checkes
    $('#assign_proxy_form').submit(function( e ){
        if( $('#assign_proxies_to_external').val() == 'yes' ){
             if( $('#external_name').val() == '' ){
                e.preventDefault();
                $('#external_name').addClass('is-invalid');
             }else{
                $('#external_name').removeClass('is-invalid');
             }

             if( $('#external_email').val() == '' ){
                e.preventDefault();
                $('#external_email').addClass('is-invalid');
             }else{
                $('#external_email').removeClass('is-invalid');
             }
        }
         
    })

    // raffle processing
    var winners = [];
    $('body').on( 'click', '.run_the_raffle', function( e ){

        $('body').append('<div class="big_loader"></div>');
        setTimeout(function () {

        var list_of_items = $('#possible_raffle_options').val();
        list_of_items = list_of_items.split("\n");

        var new_values = [];
        $.each( list_of_items, function( index, value ){
            if( value == '' ){ return; }
            new_values.push( value );
        })

        var items_count = new_values.length;
        console.log( items_count );

        var entered_number = parseInt( $('#winners_amount').val() );
        console.log( entered_number );

        if( items_count < entered_number ){
            $('.winner_number_error').removeClass('d-none');
        }else{
            $('.winner_number_error').addClass('d-none');

            winners = getRandom(new_values, entered_number);
            var out_string = '';
            $.each(winners, function(index, value){
                out_string += '<li>'+value+'</li>';
            })
            $('.results_list .raffle_results').html('<ul>'+out_string+'</ul>');
            $('.step_1_block').fadeOut().addClass('d-none');
            $('.step_2_block').fadeIn().removeClass('d-none');
        }

        
            $('.big_loader').replaceWith('');
        }, 1000);

        
    });


    
    $('body').on( 'click', '.run_another_raffle', function( e ){
        $('body').append('<div class="big_loader"></div>');
        setTimeout(function () {
            $('.step_1_block input, .step_1_block textarea').val('');
            $('.step_1_block').fadeIn().removeClass('d-none');
            $('.step_2_block').fadeOut().addClass('d-none');
            $('.big_loader').replaceWith('');
        },1000);
    })

    // run with last option
    $('body').on( 'click', '.run_another_raffle_last_option', function( e ){
        $('body').append('<div class="big_loader"></div>');
        setTimeout(function () {
            $('.step_1_block input').val('1');
            $('.step_1_block').fadeIn().removeClass('d-none');
            $('.step_2_block').fadeOut().addClass('d-none');
            $('.big_loader').replaceWith('');
        },1000);
    })
    // run with last option
    $('body').on( 'click', '.run_another_raffle_remove_winners', function( e ){
        $('body').append('<div class="big_loader"></div>');
        setTimeout(function () {
            $('.step_1_block input').val('1');

            // remove winners
            var list_of_items = $('#possible_raffle_options').val();
            list_of_items = list_of_items.split("\n");

            var new_values = [];
            $.each( list_of_items, function( index, value ){
                if( winners.includes( value ) ){ return; }
                new_values.push( value );
            })

            
            $('.step_1_block textarea').val( new_values.join( "\n" ) );
            

            $('.step_1_block').fadeIn().removeClass('d-none');
            $('.step_2_block').fadeOut().addClass('d-none');
            $('.big_loader').replaceWith('');
        },1000);
    })
 

    // random results
    function getRandom(arr, n) {
        var result = new Array(n),
            len = arr.length,
            taken = new Array(len);
        if (n > len)
            throw new RangeError("getRandom: more elements taken than available");
        while (n--) {
            var x = Math.floor(Math.random() * len);
            result[n] = arr[x in taken ? taken[x] : x];
            taken[x] = --len in taken ? taken[len] : len;
        }
        return result;
    }

    // patch for modal close
    $('body').on('keypress', function( у ){
        
    })
    $('body').bind('keypress', function(e) {
        var code = e.keyCode || e.which;
        console.log( e.key );
        if(code == 27) { 
            $.fancybox.getInstance().close();
            
        }
        console.log('ESC');
    });

    // iframe patch
    $(function() {
        if (window.self != window.top) {
          $(document.body).addClass("in-iframe");
        }
    });

    // process shortcodes
     // save value
    $('body').on( 'click', '.shortcode_link', function( e ){
        e.preventDefault();

        var data = {
            shortcode  : $(this).attr('data-shortcode'),
            iframe_url  : $(this).attr('data-iframe'),
            security : wvp_local_data.nonce,
            action : 'process_shortcode_action'
        }
 
        jQuery.ajax({url: wvp_local_data.ajaxurl,
                type: 'POST',
                data: data,            
                beforeSend: function(msg){
                        jQuery('body').append('<div class="big_loader"></div>');
                    },
                    success: function(msg){
                        console.log( msg );
                        
                        jQuery('.big_loader').replaceWith('');
                        var obj = jQuery.parseJSON( msg );
                        if( obj.result == 'success' ){
                            $('#dynamic_content_block').html( obj.html );
                            $('#fk_link').click();
                        } 
                        
                    } , 
                    error:  function(msg) {
                        console.log( msg );		
                    }          
            });
        
    })

    // sortable table
	if( $('.sortable_table').length > 0 ){
        $('table.sortable_table').tablesort();
        $('table.sortable_table').on('tablesort:complete', function(event, tablesort) {
           
            $('table.sortable_table').each(function(){
                var cnt = 1;
                $('tr td:first-child', this).each(function(){
                    $(this).html( cnt );
                    cnt++;
                })
            })
            
        });

        $('thead th.number').data('sortBy', function(th, td, tablesort) {
            return parseFloat( td.attr('data-sort-value') );
        });
    }
	if( $('.selectizer').length > 0 ){
        $('.selectizer').selectize({
            sortField: 'text'
        });
    }
    
    
    /** V1.5 Submission validation */
    $('body').on('change', '.multi_user_check', function(){
        var this_pnt = $(this);
        var parent = $(this).parents('form');
        var type = $('#poll_type', parent).val();
        if( type == 'multi' ){
           // console.log( $(this).attr('data-status') );
            if( $(this).attr('data-status') == 'on' ){
                $(this).addClass('current');
                // drop all fields
                $('.multi_user_check:not(.current)', parent).each(function(){
                    //$(this).attr('checked', false);
                    $(this).attr('id', 'tmpMark');
                    document.getElementById("tmpMark").checked = false;
                    $('#tmpMark').attr('id', false);

                    if( this_pnt.is(':checked') ){
                        if( $(this).attr('data-status') == 'off' ){
                            $(this).attr( 'disabled', true );
                        }
                    }else{
                        if( $(this).attr('data-status') == 'off' ){
                            $(this).attr( 'disabled', false );
                        }
                    }
                    
                })
                $(this).removeClass('current');
            }
            if( $(this).attr('data-status') == 'off' ){
                // check if any with ON selected
                var has_on = 0;
                $('.multi_user_check', parent).each(function(){
                    if( $(this).attr('data-status') == 'on' && $(this).is(':checked') ){
                        has_on = 1;
                    }
                })

                if( has_on == 1 ){
                    $(this).attr('checked', false);
                }
            }
           
        }
    })

    // submission check
    $('.submission_vote_form').submit(function( e ){
      

        if( $('#poll_type', this).val() == 'multi' ){
            var min_variants = $('#min_variants', this).val();
            var max_variants = $('#max_variants', this).val();
            console.log( min_variants );
            console.log( max_variants );
            // get current values
            var all_submissions = 0;
            $('input[type="checkbox"]', this).each(function(){
                if( $(this).is(':checked') == true ){
                    all_submissions++;
                }
            })
      
            if( all_submissions >= min_variants && all_submissions <= max_variants  ){
             
            }else{
                var message = wvp_local_data.pick_variant;
                message = message.replace('%s1', min_variants);
                message = message.replace('%s2', max_variants);
                alert( message );
                e.preventDefault();
            }
        }    
    })

    // fron user filters
    $('body').on('change', '#user_type', function(){
        var parent = $(this).parents('.currentusers_container');

        if( $(this).val() == 'all' ){
            $('table tr.single_user', parent).fadeIn();
        }
        if( $(this).val() == 'online' ){
            $('table tr.single_user', parent).hide();
            $('table tr.user_is_online', parent).fadeIn();
        }
        if( $(this).val() == 'offline' ){
            $('table tr.single_user', parent).hide();
            $('table tr.user_is_offline', parent).fadeIn();
        }

        
    })
    

    /**
     * Process links with aoutomodal
     */
    $('body').on('click', 'a', function( e ){
        var stringToSearch = 'popup=1';
        var current_url = $(this).attr('href');

        if ( current_url.indexOf(stringToSearch) > -1)  {
            e.preventDefault();
            current_url = current_url.replace( stringToSearch, '' )
            console.log(  current_url );

            $('#dynamic_content_block').html( '<iframe src="'+current_url+'" style="width:100%; height:600px;"></iframe>' );
            $('#fk_link').click();
        }
    })


    
    
}) // global end

function SetCookie(cookieName,cookieValue,nDays) {
    var today = new Date();
    var expire = new Date();
    if (nDays==null || nDays==0) nDays=1;
    expire.setTime(today.getTime() + 3600000*24*nDays);
    document.cookie = cookieName+"="+escape(cookieValue)
                    + ";expires="+expire.toGMTString()+"; path=/";;
   }