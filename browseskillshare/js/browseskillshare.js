(function( BrowseManager, $, undefined ) {
    //Private Property
    var loadingmessage;
    
    // Public Property
    BrowseManager.filters = new Array();

    //Public Method
    BrowseManager.filter_skillshare_content = function(browsetype, skillshareoffset) {
        skillshareoffset = typeof skillshareoffset !== 'undefined' ? skillshareoffset : 0;
        var pd = {'filter': 1, 'offset': skillshareoffset};
        $.each(BrowseManager.filters, function(index, val) {
            pd[val['name']] = val['value'];
        });

        loadingmessage.removeClass('hidden');
        sendjsonrequest(config['wwwroot'] + 'artefact/browseskillshare/browseskillshare.json.php', pd, 'POST', function(data) {
            loadingmessage.addClass('hidden');
            $('#skillsharelistings').replaceWith(data.data.tablerows);
            $('#pagination').html(data.data.pagination);
            connect_fullscreen_links();
        });      
    };

    //Private Method
    function init() {
        loadingmessage = $('#loadingmessage');
        connect_fullscreen_links();
        connect_enter_event();
        connect_add_filter_options();
        connect_autocomplete();

        // set filter checkboxes to be buttons
        $('input.checkbox')
            .button({
            icons: { secondary: "ui-icon-circle-plus" }
            })
            .click(function () {
                if ( $(this).is(':checked')) {
                    $(this).button("option", "icons", {secondary: "ui-icon-circle-close"});
                    var parenttype = $(this).closest('.filtersection').attr('id');
                    var filtertype = 'college';
                    if (parenttype.indexOf('sharetype') >= 0) {
                        filtertype = 'sharetype';
                    }
                    var inputval = $(this).val();
                    add_filter(filtertype, inputval);
                }
                else {
                    $(this).button("option", "icons", {secondary: "ui-icon-circle-plus"});
                    var parenttype = $(this).closest('.filtersection').attr('id');
                    var filtertype = 'college';
                    if (parenttype.indexOf('sharetype') >= 0) {
                        filtertype = 'sharetype';
                    }
                    var inputval = $(this).val();
                    remove_active_filter(filtertype, inputval);
                }
            });

        var History = window.History; // Note: We are using a capital H instead of a lower h
        if (!History.enabled) {
            return false;
        }
        
        History.Adapter.bind(window, 'statechange', function() {
            if ($('#messages')[0]) { $('#messages').remove(); }
            if ($('#skillsharefullscreen')[0]) { $('#skillsharefullscreen').remove(); }
            if ($('#overlay')[0]) { $('#overlay').remove(); }
        });
        
        check_url_showfullscreen();
    }

    function check_url_showfullscreen() {
        // check for hash value (html4) and History.js adjusted value (html5)
        var currhash = History.getHash();
        var fragment = currhash;
        if (!currhash.length) {
            fragment = $(location).attr('pathname');
        }
        var listing = fragment.substr(fragment.lastIndexOf('listing-') + 8);
        // check for numeric value
        if (!isNaN(parseFloat(listing)) && isFinite(listing)) {
            showfullscreen(listing);
        }
    }

    function connect_fullscreen_links() {
        $('.listing').each(function() {
            var id = $(this).attr('name');
            $('.exampleimages', this).click(function(e) {
                showfullscreen(parseInt(id));
            });
            $('.viewmore', this).click(function(e) {
                showfullscreen(parseInt(id));
            });
            $('.listingtitle.list h3', this).click(function(e) {
                showfullscreen(parseInt(id));
            });
        });
        
    }

    function connect_close_button() {
        $('#closelisting').click(function(event) {
            $('#skillsharefullscreen').remove();
            $('#overlay').remove();
            History.back();
        });    
    }

    function connect_enter_event() {
        $("#filter-keyword, #filter-course").keypress(function(event) {
          var keycode = (event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode));
            if ( keycode == 13 ) {
               event.preventDefault();
               event.stopPropagation();
               add_filter($(this).attr('name'), $(this).val());
               $(this).val('');
               $(this).focus();
             $('#filter-course-container').hide();
             hide_filter_course_container();
             }
          });
    }

    function connect_autocomplete() {
        var pd = {'autocomplete': 1,
                   'field' : 'course'
                 };
        $('#filter-course').autocomplete({
            minLength: 3,
            source: function(request, response) {
                pd['term'] = request['term'];
                sendjsonrequest(config['wwwroot'] + 'artefact/browseskillshare/autocomplete.json.php', pd, 'POST', function(data) {
                    response(data.courses);
                });
            }
        });
    }

    function hide_filter_course_container() {
        $('#filter-course-container').hide();
        var a = $('#activate-course-search').find('a');
        a.removeClass('chzn-single-with-drop');
        a.parent().removeClass('chzn-container-active');
    }

    function show_filter_course_container() {
        $('#filter-course-container').show();
        var a = $('#activate-course-search').find('a');
        a.addClass('chzn-single-with-drop');
        a.parent().addClass('chzn-container-active');
    }

    function toggle_filter_course_container() {
        $('#filter-course-container').toggle();
        var a = $('#activate-course-search').find('a');
        if ($('#filter-course-container').is(":visible")) {
            a.addClass('chzn-single-with-drop');
            a.parent().addClass('chzn-container-active');
        } else {
            a.removeClass('chzn-single-with-drop');
            a.parent().removeClass('chzn-container-active');
        }
    }

    function connect_add_filter_options() {
        $('.add-text-filter-button').each(function() {
            connect_add_text_button($(this));
        });
        $('#filter-sharetype-container, #filter-college-container, #filter-keyword').click(function() {
            hide_filter_course_container();
        });
        $('.chzn-select').chosen({disable_search_threshold: 10}).change(function() {
            var id = $(this).attr('id');
            var type = id.substr(id.lastIndexOf('-')+1);
            add_filter(type, $(this).val());
        });
        $('#activate-course-search').click(function() {
            toggle_filter_course_container();
        });
        $('#query-button-course').click(function() {
            hide_filter_course_container();
        });
    }

    function connect_add_text_button(button) {
        button.click(function(event) {
             event.preventDefault();
            var type = $(button).val();
            var inputval = $(button).prev('input').val();
            add_filter(type, inputval);
              event.stopPropagation();
        });
    }

    function add_filter(addtype, value) {
        // check for existing filters
        var alreadyExists = false;
        $('.filter-entry[name="' + addtype + '"]').each(function() {
            if (addtype == 'course') {
                if ($(this).text() == value) {
                    alreadyExists = true;
                    return false; // this just breaks the each loop
                }
            }
            if ($(this).attr('value') == value) {
                alreadyExists = true;
                return false; // this just breaks the each loop
            }
        });

        if (alreadyExists || !value.length) {
            return false;
        }

        var temp = $('<div>').addClass('filter-entry');
        if (addtype == 'keyword') {
            temp.html(value);
            temp.attr('name', addtype);
            temp.attr('value', value);
            add_active_filter(temp);
            $('#active-filters-container').show();
        } else if (addtype == 'course') {
            var pd = {'autocomplete': 1,
                    'field' : 'courseid',
                    'term'  : $('#filter-course').val()
                  };
             sendjsonrequest(config['wwwroot'] + 'artefact/browseskillshare/autocomplete.json.php', pd, 'POST', function(data) {
                if (!data.courseid.length) {
                    return false;
                }
                 temp.html(value);
                temp.attr('name', addtype);
                temp.attr('value', data.courseid);
                add_active_filter(temp);
                $('#active-filters-container').show();
             });
        } else {
            //sharetype or college
            temp.html($('#filter_' + addtype + '_chzn').find('span').html());
            temp.attr('name', addtype);
            temp.attr('value', value);
            add_active_filter(temp);
            $('.chzn-select').val([]).trigger('liszt:updated');
            $('#active-filters-container').show();
        }  
    }

    function add_active_filter(temp) {
        var filterwrapper = $('<div>').addClass('filter-entry-wrapper fl');
        filterwrapper.append(temp);
        var remove = $(".remove-filter input").clone();
        var removediv = $('<div>').addClass('remove-filter-entry');
        removediv.attr('name', temp.attr('name'));
        removediv.attr('value', temp.val());
        removediv.append(remove);
        filterwrapper.append(removediv);
        // update list of active filters
        BrowseManager.filters.push( {'name':temp.attr('name'), 'value':temp.val()} );
        $("#active-filters").append(filterwrapper);
        connect_remove_button(remove);
        
        if ($('#active-filters .filter-entry').length == 2) {
            var removeallwrapper = $('<div>').attr('id', 'remove-all-wrapper').addClass('remove-all-wrapper fr');
            var removealltext = $('<div>').attr('id', 'remove-all-filter-entries');
            var removeallbutton = $('<div>').attr('id', 'remove-all-button').addClass('remove-filter-entry');
            var removebutton = $(".remove-filter input").clone();
            removealltext.html('Clear all filters');
            removeallbutton.append(removebutton);
            removeallwrapper.prepend(removealltext);
            removeallwrapper.prepend(removeallbutton);
            $("#active-filters").prepend(removeallwrapper);
            connect_remove_all_button(removebutton);
        }
        refresh_content(0);
    }

    function connect_remove_button(button) {
        button.click(function(event) {
            var parent = button.parent('.remove-filter-entry');
                
            if ($(parent).attr('name') == 'course') {
                $('#filter-course').val('');
            }
            else if ($(parent).attr('name') == 'keyword') {
                $('#filter-keyword').val('');
            }
            button.closest('.filter-entry-wrapper').remove();
            // invert result of grep, returns array with elements which don't match item to be removed
            var newfilters = $.grep(BrowseManager.filters, function(n, i) {
            	return (n.name == $(parent).attr('name') && n.value == $(parent).val());
            }, true);
            BrowseManager.filters = newfilters;

            if ($('#active-filters .filter-entry').length < 2) {
                $('#remove-all-wrapper').remove();
            }
            if ($('#active-filters .filter-entry').length < 1) {
                $('#active-filters-container').hide();
            }
            refresh_content(0);
        });
    }

    function connect_remove_all_button(button) {
        button.click(function(event) {
            $('#active-filters .filter-entry-wrapper').each(function() {
                $(this).remove();
            });

            $('#remove-all-wrapper').remove();
            $('#filter-course').val('');
            $('#filter-keyword').val('');
            $('#active-filters-container').hide();
            while (BrowseManager.filters.length > 0) {
            	BrowseManager.filters.pop();
            }
            refresh_content(0);
        });
    }

    function refresh_content(offset) {
        $('#loading-graphic').show();
        offset = typeof offset !== 'undefined' ? offset : 0;
        var filters = {};
        $("#active-filters .filter-entry").each( function() {
            var name = $(this).attr('name');
            var val = $(this).attr('value');
            if (filters[name] && filters[name].indexOf(val) == -1) {
                if (name=='course') {
                    // some course names will return multiple ids
                    // cater for this when building db query
                    filters[name] += ";" +val;
                } else {
                    filters[name] += "," +val;
                }
            } else {
                filters[name] = val;
            }
        });
        var pd = {'filter': 1, 'offset': offset};
        $.each(filters, function(name, value) {
            pd[name] = value;
        });

        sendjsonrequest(config['wwwroot'] + 'artefact/browseskillshare/browseskillshare.json.php', pd, 'POST', function(data) {
                $('#wire').addClass('hidden'); 
                $('#skillsharelistings').removeClass('hidden');
                $('#skillsharelistings').html(data.data.tablerows);
                if (!$('#pagination').length) {
                    var pag = $('<div>').attr('id', 'pagination').html(data.data.pagination);
                    $('#skillsharelistings').prepend(pag);
                } else {
                    $('#pagination').html(data.data.pagination);
                }
                connect_fullscreen_links();
        });
        $('#loading-graphic').hide();
    }

    function showfullscreen(id) {
        if (History.getState()['url'] != config.wwwroot + "artefact/browseskillshare/listing-" + id) {
            if ($.browser.msie) {
                History.pushState(null, null, "/listing-" + id);
            } else {
                History.pushState(null, "Skillshare listing", config.wwwroot + "artefact/browseskillshare/listing-" + id);
            }
        }
        var pd = {'fullscreen': 1,
                  'id' : id
                 };
        sendjsonrequest(config['wwwroot'] + 'artefact/browseskillshare/skillsharefullscreen.json.php', pd, 'POST', function(data) {
            var fullscreen = $('<div>').attr('id', 'skillsharefullscreen').html(data.data.html);
            fullscreen.addClass('hidden');
            $('body').prepend($('<div>').attr('id', 'overlay'));
            $('body').append(fullscreen);
            fullscreen.removeClass('hidden');
            fullscreen.css('z-index', 9998);
            set_galleriffic(); // initialise
            $('#overlay').click(function(event) {
                fullscreen.remove();
                $(this).remove();
                History.back();
            });
            connect_close_button();
        });
    };

    function set_galleriffic() {
        // We only want these styles applied when javascript is enabled
        $('div.content').css('display', 'block');

        // Initially set opacity on thumbs and add
        // additional styling for hover effect on thumbs
        var onMouseOutOpacity = 0.67;
        $('#thumbs ul.thumbs li').opacityrollover({
            mouseOutOpacity:   onMouseOutOpacity,
            mouseOverOpacity:  1.0,
            fadeSpeed:         'fast',
            exemptionSelector: '.selected'
        });
        // Initialize Advanced Galleriffic Gallery
        var gallery = $('#thumbs').galleriffic({
            delay:                     2500,
            numThumbs:                 4,
            preloadAhead:              4,
            enableTopPager:            false,
            enableBottomPager:         false,
            imageContainerSel:         '#slideshow',
            controlsContainerSel:      '#controls',
            captionContainerSel:       '#caption',
            loadingContainerSel:       '#loading',
            renderSSControls:          true,
            renderNavControls:         false,
            playLinkText:              'play slideshow',
            pauseLinkText:             'pause slideshow',
            prevLinkText:              '&lsaquo; previous',
            nextLinkText:              'next &rsaquo;',
            nextPageLinkText:          'Next &rsaquo;',
            prevPageLinkText:          '&lsaquo; Prev',
            enableHistory:             true,
            autoStart:                 false,
            syncTransitions:           true,
            defaultTransitionDuration: 900,
            onSlideChange:             function(prevIndex, nextIndex) {
                // 'this' refers to the gallery, which is an extension of $('#thumbs')
                this.find('ul.thumbs').children()
                    .eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
                    .eq(nextIndex).fadeTo('fast', 1.0);
            },
            onPageTransitionOut:       function(callback) {
                this.fadeTo('fast', 0.0, callback);
            },
            onPageTransitionIn:        function() {
                var prevPageLink = this.find('a.prev').css('visibility', 'hidden');
                var nextPageLink = this.find('a.next').css('visibility', 'hidden');
                
                // Show appropriate next / prev page links
                if (this.displayedPage > 0)
                    prevPageLink.css('visibility', 'visible');

                var lastPage = this.getNumPages() - 1;
                if (this.displayedPage < lastPage)
                    nextPageLink.css('visibility', 'visible');

                this.fadeTo('fast', 1.0);
            }
        });
    }

    $(document).ready(function() {
        init();
    });

}( window.BrowseManager = window.BrowseManager || {}, jQuery ));