"use strict";

var params = {
    firstHour: 8,
    lastHour: 22,

    baseUrl: "/json/",

    fullCalendarLocale: {
        monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
        monthNamesShort: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc"],
        dayNames: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
        dayNamesShort: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
    },
}

var utils = {
    initCalendar: function(opt){
        opt = opt ? opt : {};

        var defaults = {
            theme: true,
            firstDay: 1,
            defaultView: "agendaWeek",
            allDaySlot: false,
            slotMinutes: 5,
            minTime: params.firstHour,
            maxTime: params.lastHour,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            weekends: true,

            //Format stuff to make it in french
            timeFormat: {
                agenda: "H'h'mm{ - H'h'mm}",
                '': "H('h'mm)"
            },
            titleFormat: {
                week: "MMMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}"
            },
            axisFormat: "H'h'(mm)",
            buttonText:{
                prev:     '&nbsp;&#9668;&nbsp;',  // left triangle
                next:     '&nbsp;&#9658;&nbsp;',  // right triangle
                prevYear: '&nbsp;&lt;&lt;&nbsp;', // <<
                nextYear: '&nbsp;&gt;&gt;&nbsp;', // >>
                today:    'aujourd\'hui',
                month:    'mois',
                week:     'semaine',
                day:      'jour'
            },

           //editable: true,
        };

        $.extend(true, opt, defaults, params.fullCalendarLocale);

        return $('#calendar').fullCalendar(opt);
    }
};
