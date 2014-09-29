"use strict";

var admin = (function(){
    var calendar;
    var userCombo;
    var slotCombo;
    var users;
    var emptyDialog;

    var userComboData;
    var userComboUser;
    var userDialog;

    var slotComboUser;
    var selectedSlot;

    //--------
    //--------Page and content
    //--------
    var reloadComboBoxes = function(){
        $(userCombo).autocomplete("option", "source", userComboData);
        $(slotCombo).autocomplete("option", "source", userComboData);
    }

    var populateEvent = function(e){
        e.allDay = false;
        if(e.user == null){
            e.color = "#00aa00";
            e.title = "Libre";
        }else{
            e.color = "#0000aa";
            e.title = users[e.user].name
        }
        if(selectedSlot === e){
            e.color = "#aa0000";
        }
    }

    var registerUser = function(u){
        users[u.id] = u;
        u.slot = null;
    }

    //--------
    //--------Slots
    //--------
    var onSlotComboSelect = function(event, ui){
        slotComboUser = ui.item.user;
    }

    var onEventClick = function(event, js, v){
        var oldSlot = selectedSlot;
        selectedSlot = event;
        
        if(oldSlot != null){
            populateEvent(oldSlot);
            calendar.fullCalendar("updateEvent", [oldSlot]);
        }
        populateEvent(selectedSlot);
        calendar.fullCalendar("updateEvent", [selectedSlot]);
    }

    var onDestroySlotLoaded = function(data, slot){
        data = eval(data);

        if(selectedSlot === slot){
            selectedSlot = null;
        }

        if(slot.user != null){
            slot.user.slot = null;
        }

        calendar.fullCalendar("removeEvents", slot._id);
        selectedSlot = null;
    }

    var onDestroySlotClicked = function(){
        if(selectedSlot == null){
            return;
        }

        var slot = selectedSlot;

        $.ajax(params.baseUrl + "admin/creneau/suppr/" + slot.id, {
            success: function(data){
                onDestroySlotLoaded(data, slot);
            },
            /*error: bigError,*/
            dataType: "json"
        });
    }

    var onAssignSlotLoaded = function(data, slot, user){
        data = eval(data);
        
        if(slot.user != null){
            slot.user.slot = null;
        }

        if(user.slot != null){
            user.slot.user = null;
            populateEvent(user.slot);
            calendar.fullCalendar("updateEvent", [user.slot]);
        }

        slot.user = user.id;
        user.slot = slot;
        populateEvent(slot);
        calendar.fullCalendar("updateEvent", [slot]);
    }

    var onAssignSlotClicked = function(){
        if(selectedSlot == null || slotComboUser == null){
            return;
        }

        var slot = selectedSlot;
        var user = slotComboUser;

        $.ajax(params.baseUrl + "admin/assign/" + user.id + "/" + slot.id, {
            success: function(data){
                onAssignSlotLoaded(data, slot, user);
            },
            /*error: bigError,*/
            dataType: "json"
        });
    }

    //--------
    //--------Users
    //--------
    var onUserComboSelect = function(event, ui){
        var user = ui.item.user;
        userComboUser = user;

        var text;
        if(user.slot == null){
            text = "Pas de créneau.";
        }else{
            var date = user.slot.start;
            text = $.fullCalendar.formatDate(date, "'Le' d MMM yyyy 'à' H'h'mm.", params.fullCalendarLocale)
        }
        $("#user-when-slot").text(text);

        if(user.hruid == null){
            $("#user-other-buttons").show();
        }else{
            $("#user-other-buttons").hide();
        }
    }

    var onModifyUserLoaded = function(data, user, name, email){
        data = eval(data);

        user.name = name;
        user.email = email;

        if(user.slot != null){
            populateEvent(user.slot);
            calendar.fullCalendar("updateEvent", [user.slot]);
        }

        regenerateComboData();
        reloadComboBoxes();
    };

    var onModifyUserClicked = function(){
        if(userComboUser == null){
            return;
        }

        var user = userComboUser;

        $("#user-modal-name").val(user.name);
        $("#user-modal-email").val(user.email);

        userDialog.dialog("option", {
            title: "Modification de " + user.name,
            buttons: {
                "Modifier l'utilisateur": function(){
                    var name = $("#user-modal-name").val();
                    var email = $("#user-modal-email").val();
                    $.ajax(params.baseUrl + "admin/user/modif/" + user.id, {
                        type: "POST",
                        data: {
                            name: name,
                            email: email,
                        },
                        success: function(data){
                            onModifyUserLoaded(data, user, name, email);
                        },
                        /*error: bigError,*/
                        dataType: "json"
                    });
                    $(this).dialog("close");
                },
                "Annuler": function(){
                    $(this).dialog("close");
                }
            }
        }).dialog("open");
    }

    var onNewUserLoaded = function(data, name, email){
        data = eval(data);

        if(! data.success){
            return;
        }

        var user = {
            id: data.id,
            slot: null,
            hruid: null,
            name: name,
            email: email
        };

        users[user.id] = user;

        regenerateComboData();
        reloadComboBoxes();
    }

    var onNewUserClicked = function(){
        var user = userComboUser;
        $("#user-modal-name").val("");
        $("#user-modal-email").val("@polytechnique.edu");

        userDialog.dialog("option", {
            title: "Ajout d'un non-Frankiz",
            buttons: {
                "Créer l'utilisateur": function(){
                     var name = $("#user-modal-name").val();
                     var email = $("#user-modal-email").val();
                     $.ajax(params.baseUrl + "admin/user/creer", {
                        type: "POST",
                        data: {
                            name: name,
                            email: email,
                        },
                        success: function(data){
                            onNewUserLoaded(data, name, email);
                        },
                        /*error: bigError,*/
                        dataType: "json"
                    });

                    $(this).dialog("close");
                },
                "Annuler": function(){
                    $(this).dialog("close");
                }
            }
        }).dialog("open");
    }

    var onDeleteUserLoaded = function(data, user){
        data = eval(data);

        if(! data.success){
            return;
        }

        if(user.slot != null){
            user.slot.user = null;
            populateEvent(user.slot);
            calendar.fullCalendar("updateEvent", [user.slot]);
        }

        if(slotComboUser === user){
            slotComboUser = null;
        }
        if(userComboUser === user){
            userComboUser = null;
        }

        delete users[user.id];

        regenerateComboData();
        reloadComboBoxes();
    }

    var onDeleteUserClicked = function(){
        if(userComboUser == null){
            return;
        }

        var user = userComboUser;

        emptyDialog.dialog("option", {
            title: "Suppression de " + user.name,
            buttons: {
                "Supprimer": function(){
                    $.ajax(params.baseUrl + "admin/user/suppr/" + user.id, {
                        success: function(data){
                            onDeleteUserLoaded(data, user);
                        },
                        /*error: bigError,*/
                        dataType: "json"
                    });

                   $(this).dialog("close");
                },
                "Annuler": function(){
                    $(this).dialog("close");
                }
            }
        }).dialog("open");
    }

    //--------
    //--------Reload data
    //--------

    var regenerateComboData = function(){
        userComboData = [];

        for(var i in users){
            var u = users[i];

            //Add the combo box data
            var data = {label: u.name, user: u};
            if(u.hruid == null){
                data.value = u.name;
            }else{
                data.value = u.hruid;
            }
            userComboData.push(data);
        }
    }
 

    var onListLoaded = function(data){
        data = eval(data);

        selectedSlot = null;
        userComboUser = null;
        slotComboUser = null;
        userCombo.val("");
        slotCombo.val("");

        users = {};
        for(var i in data.users){
            registerUser(data.users[i]);
        }

        regenerateComboData();

        var events = data.creneaux;
        for(var i in events){
            var e = events[i];
            if(e.user != null){
                users[e.user].slot = e;
            }
            populateEvent(events[i]);
        }

        reloadComboBoxes();

        //Update the calendar
        calendar.fullCalendar("removeEvents");
        calendar.fullCalendar("addEventSource", events);
    }

    var askReload = function(){
         $.ajax(params.baseUrl + "admin/list", {
            success: onListLoaded,
            /*error: bigError,*/
            dataType: "json"
        });
    }

    //--------
    //--------External & Drag and Drop
    //--------
    var slotDefaultDuration = 10;
    var droppableParams = [
        {number: 1, name: "10 Minutes = 1 Slot"},
        {number: 3, name: "30 Minutes = 3 Slot"},
        {number: 6, name: "1 Heure = 6 Slot"},
        {number: 12, name: "2 Heures = 12 Slot"},
        {number: 24, name: "4 Heures = 24 Slot"},
    ];

    var initDroppables = function(){
        var droppables = $("#sidebar div.droppable-slot");

        //Populate the div elements
        for(var i = 0; i < droppables.length; i++){
            var div = $(droppables[i]);
            var param = droppableParams[i];
            div.data("number", param.number);
            div.text(param.name);

			div.draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});
        }
    }

    var onDrop = function(date){
        var number = $(this).data("number");
        var offset = 1000*60*slotDefaultDuration;
        var baseTime = date.getTime();
        console.log(baseTime);
        date.setHours(params.lastHour);
        date.setMinutes(0);
        var maxTime = date.getTime();

        for(var i = 0; i < number; i++){
            if(baseTime + offset > maxTime){
                break;
            }
            tryAddSlot(baseTime, baseTime + offset);
            baseTime += offset;
        }
    }

    var tryAddSlot = function(start, end){
        start /= 1000;
        end /= 1000;
        $.ajax(params.baseUrl + "admin/creneau/creer/" + start + "/" + end, {
            success: function(data){
                onAddSlotLoaded(data, start, end);
            },
            /*error: bigError,*/
            dataType: "json"
        });
    }

    var onAddSlotLoaded = function(data, start, end){
        data = eval(data);

        if(! data.success){
            return;
        }

        var event = {
            start: start,
            end: end,
            id: data.id,
            user: null
        };
        populateEvent(event);
        calendar.fullCalendar("addEventSource", [event]);
    }

    var onChangeSlotLoaded = function(data, revert){
        data = eval(data);
        if(! data.success){
            revert();
        }
    }

    var tryUpdateEventSize = function(event, revert){
        var start = event.start.getTime() / 1000;
        var end = event.end.getTime() / 1000;
        $.ajax(params.baseUrl + "admin/creneau/modif/" + event.id + "/" + start + "/" + end, {
            success: function(data){
                onChangeSlotLoaded(data, revert);
            },
            /*error: bigError,*/
            dataType: "json"
        });
    }

    var onEventDrop = function(event, d, m, ad, revert, js, ui, v){
        tryUpdateEventSize(event, revert);
    };

    var onEventResize = function(event, d, m, revert, js, ui, v){
        tryUpdateEventSize(event, revert);
    };

    //--------
    //--------Public Function
    //--------
    return {
        init: function(){
            calendar = utils.initCalendar({
                droppable: true,
                drop: onDrop,
                editable: true,
                eventDrop: onEventDrop,
                eventResize: onEventResize,
                eventClick: onEventClick,
            });
           
            userCombo = $("#user-combo").autocomplete({
                source: [],
                select: onUserComboSelect,
            }); 
            $("#user-modify-button").button().click(onModifyUserClicked);
            $("#user-new-button").button().click(onNewUserClicked);
            $("#user-delete-button").button().click(onDeleteUserClicked);
            $("#user-other-buttons").hide();
            userDialog = $("#user-modal").dialog({
                autoOpen: false,
                height: 250,
                width: 350,
                modal: true,
            });

            emptyDialog = $("<div>").dialog({
                autoOpen: false,
                height: 100,
                width: 250,
                modal: true,
            });
            $("#reload-button").button().click(askReload);
            $("#visitor-button").button().click(function(){
                location.href = "home";
            });

            slotCombo = $("#slot-combo").autocomplete({
                source: [],
                select: onSlotComboSelect
            }); 
            $("#slot-destroy-button").button().click(onDestroySlotClicked);
            $("#slot-assign-button").button().click(onAssignSlotClicked);
            
            initDroppables();

            askReload();
        },
    }
})();
