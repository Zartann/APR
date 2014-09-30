"use strict";

//VISITORS

var visitor = (function(){
    var cid;
    var name;
    var calendar;
    var slot;

    var styleFree = function(e){
        e.color = "#00aa00";
        e.title = "Libre";
        e.available = true;
    }

    var styleTaken = function(e){
        e.color = "#666666";
        e.title = "Pris";
        e.available = false;
    }

    var styleMe = function(e){
        e.color = "#aa0000";
        e.title = "Vous ! (" + name + ")";
        e.available = false;
    }

    var reload = function(data){
        //TODO: idealement on devrait mettre le nom et la date du créneau du gars en haut mais .... flemme
        cid = data.id;
        name = data.user;

        var events = data.creneaux;

        for(var i in events){
            var e = events[i];

            e.allDay = false;
            if(e.id == cid){
                slot = e;
                styleMe(e);
            }else if(e.available){
                styleFree(e);
            }else{
                styleTaken(e);
            }
        }
        calendar.fullCalendar("removeEvents");
        calendar.fullCalendar("addEventSource", events);
    }

    var onListLoaded = function(data){
        reload(eval(data));
    }

    var onReserveLoaded = function(data, newSlot){
        data = eval(data);
        if(data.success){
            var oldSlot = slot;
            slot = newSlot;
            styleMe(newSlot);
            cid = newSlot.id;
            if(oldSlot){
                styleFree(oldSlot);
                calendar.fullCalendar("updateEvent", [oldSlot, newSlot]);
            }else{
                calendar.fullCalendar("updateEvent", [newSlot]);
            }
        }else{
            alert("Impossible de réserver le créneau, soit quelqu'un l'a déjà pris (essaye de recharger la page), soit il est déjà passé.");
            //TODO signal error
        }
    }

    var onAnnulerLoaded = function(data, oldSlot){
        data = eval(data)
        if(data.success){
            if(slot === oldSlot){
                slot = null;
            }
            styleFree(oldSlot);
            calendar.fullCalendar("updateEvent", [oldSlot]);
        }
    }

    var bigError = function(){
        //alert("Erreur majeure. Recharge la page avec CTRL-MAJ-F. Si le problème persiste, contacte nous sur bug@apr.eltrai.net");
        //TODO signal error
    }

    var onEventClicked = function(e, jse, v){
        if(e.id == cid){
             $.ajax(params.baseUrl + "visitor/annuler", {
                success: function(data){
                    onAnnulerLoaded(data, e);
                },
                error: bigError,
                dataType: "json"
            });
        }
        if(!e.available){
            return;
        }
        $.ajax(params.baseUrl + "visitor/reserver/" + e.id, {
            success: function(data){
                onReserveLoaded(data, e);
            },
            error: bigError,
            dataType: "json"
        });
    }

    return {
        init: function(admin){
            calendar = utils.initCalendar({eventClick: onEventClicked});
            $.ajax(params.baseUrl + "visitor/list", {
                success: onListLoaded,
                error: bigError,
                dataType: "json"
            });

            $("#surname-button").button().click(function(){
                location.href = "register";
            });

            $("#logout-button").button().click(function(){
                location.href = "exit";
            });

            if(admin == 1){
                $("#admin-button").button().click(function(){
                    location.href = "admin";
                });
            }
        }
    };
 })();
