var i18n = jQuery.extend({}, i18n || {}, {
    dcmvcal: {
        dateformat: {
            "fulldaykey": "ddMMyyyy",
            "fulldayshow": "d L yyyy",
            "fulldayvalue": "d/M/yyyy", 
            "Md": "W d/M",
            "nDaysView": "d/M",
            "listViewDate": "d L yyyy",
            "Md3": "d L",
            "separator": "/",
            "year_index": 2,
            "month_index": 1,
            "day_index": 0,
            "day": "d",
            "sun2": "Di",
            "mon2": "Lu",
            "tue2": "Ma",
            "wed2": "Me",
            "thu2": "Je",
            "fri2": "Ve",
            "sat2": "Sa",
            "sun": "Dim",
            "mon": "Lun",
            "tue": "Mar",
            "wed": "Mer",
            "thu": "Jeu",
            "fri": "Ven",
            "sat": "Sam",
            "sunday": "Sunday",
            "monday": "Monday",
            "tuesday": "Tuesday",
            "wednesday": "Wednesday",
            "thursday": "Thursday",
            "friday": "Friday",
            "saturday": "Saturday",
            "jan": "Jan",
            "feb": "Fév",
            "mar": "Mar",
            "apr": "Avr",
            "may": "Mai",
            "jun": "Jui",
            "jul": "Jui",
            "aug": "Aoû",
            "sep": "Sep",
            "oct": "Oct",
            "nov": "Nov",
            "dec": "Déc",
            "l_jan": "Janvier",
            "l_feb": "Février",
            "l_mar": "Mars",
            "l_apr": "Avril",
            "l_may": "Mai",
            "l_jun": "Juin",
            "l_jul": "Juillet",
            "l_aug": "Août",
            "l_sep": "Septembre",
            "l_oct": "Octobre",
            "l_nov": "Novembre",
            "l_dec": "Décembre"
        },
        "no_implemented": "Pas encore implementé",
        "to_date_view": "Cliquez ici pour voir la date actuelle",
        "i_undefined": "Indéfini",
        "allday_event": "Evénement de toute la journée",
        "repeat_event": "Répeter événement",
        "time": "Heure",
        "event": "Evénement",
        "location": "Lieu",
        "participant": "Participant",
        "get_data_exception": "Erreur lors du chargement des données",
        "new_event": "Nouvel événement",
        "confirm_delete_event": "Confirmez-vous la supprésion de cet événement?",
        "confirm_delete_event_or_all": "Voulez-vous supprimer tous les événements répétés ou seulement celui-ci? \r\n Cliquez [OK] pour supprimer seulement cet événement,  et sur \"Annuler\" pour supprimer tous les événements",
        "data_format_error": "Erreur de format de donnees",
        "invalid_title": "Titre de l'événement ne peut être nul ou contenir ($<>)",
        "view_no_ready": "La visualisation n'est pas encore prete",
        "example": "par exemple, Réunion dans la chambre 107",
        "content": "Quoi",
        "create_event": "Créer événement",
        "update_detail": "Modifier les détails",
        "click_to_detail": "Voir les détails",
        "i_delete": "Supprimer",
        "i_save": "Enregistrer",
        "i_close": "Fermer",
        "day_plural": "jours",
        "others": "autres",
        "item": "",
        "loading_data":"chargement des données...",
        "request_processed":"La demande est en cours de traitement...",
        "success":"Succès!",
        "are_you_sure_delete":"Etes-vous sûr de vouloir supprimer cet événement?",
        "ok":"Accepter",
        "cancel":"Annuler",
        "manage_the_calendar":"Gérer le calendrier",
        "error_occurs":"Des erreurs se sont produits",
        "color":"Couleur",
        "invalid_date_format":"Format de date incorrect",
        "invalid_time_format":"Format d'heure incorrect",
        "_symbol_not_allowed":"$<> ne sont pas permis",
        "subject":"Sujet",
        "time":"Heure",
        "to":"A",
        "all_day_event":"Journée entière",
        "location":"Lieu",
        "remark":"Description",
        "click_to_create_new_event":"Cliquer pour créer un nouvel événement",
        "new_event":"Nouvel événement",
        "click_to_back_to_today":"Cliquez pour retourner à aujourd'hui",
        "today":"Aujourd'hui",
        "sday":"Jour",
        "week":"Semaine",
        "month":"Mois",
        "ndays":"Jours",
        "list":"List",
        "nmonth":"nMois",
        "refresh_view":"Actualiser l'image",
        "refresh":"Actualiser",
        "prev":"Préc.",
        "next":"Suiv.",
        "loading":"Chargement en cours",
        "error_overlapping":"This event is overlapping another event",
        "sorry_could_not_load_your_data":"Désolé, chargement échoué, veuillez réessayez plus tard",
        "first":"Première",
        "second":"Deuxième",
        "third":"Troisième",
        "fourth":"Quatrième",
        "last":"last",
        "repeat":"Repeat: ",
        "edit":"Edit",
        "edit_recurring_event":"Edit recurring event",
        "would_you_like_to_change_only_this_event_all_events_in_the_series_or_this_and_all_following_events_in_the_series":"Would you like to change only this event, all events in the series, or this and all following events in the series?",
        "only_this_event":"Only this event",
        "all_other_events_in_the_series_will_remain_the_same":"All other events in the series will remain the same.",
        "following_events":"Following events",
        "this_and_all_the_following_events_will_be_changed":"This and all the following events will be changed.",
        "any_changes_to_future_events_will_be_lost":"Any changes to future events will be lost.",
        "all_events":"All events",
        "all_events_in_the_series_will_be_changed":"All events in the series will be changed.",
        "any_changes_made_to_other_events_will_be_kept":"Any changes made to other events will be kept.",
        "cancel_this_change":"Cancel this change",
        "delete_recurring_event":"Delete recurring event",
        "would_you_like_to_delete_only_this_event_all_events_in_the_series_or_this_and_all_future_events_in_the_series":"Would you like to delete only this event, all events in the series, or this and all future events in the series?",
        "only_this_instance":"Only this instance",
        "all_other_events_in_the_series_will_remain":"All other events in the series will remain.",
        "all_following":"All following",
        "this_and_all_the_following_events_will_be_deleted":"This and all the following events will be deleted.",
        "all_events_in_the_series":"All events in the series",
        "all_events_in_the_series_will_be_deleted":"All events in the series will be deleted.",
        "repeats":"Repeats",
        "daily":"Daily",
        "every_weekday_monday_to_friday":"Every weekday (Monday to Friday)",
        "every_monday_wednesday_and_friday":"Every Monday, Wednesday, and Friday",
        "every_tuesday_and_thursday":"Every Tuesday, and Thursday",
        "weekly":"Weekly",
        "monthly":"Monthly",
        "yearly":"Yearly",
        "repeat_every":"Repeat every:",
        "weeks":"weeks",
        "repeat_on":"Repeat on:",
        "repeat_by":"Repeat by:",
        "day_of_the_month":"day of the month",
        "day_of_the_week":"day of the week",
        "starts_on":"Starts on:",
        "ends":"Ends:",
        "never":" Never",
        "after":"After",
        "occurrences":"occurrences",
        "summary":"Summary:",
        "every":"Every",
        "weekly_on_weekdays":"Weekly on weekdays",
        "weekly_on_monday_wednesday_friday":"Weekly on Monday, Wednesday, Friday",
        "weekly_on_tuesday_thursday":"Weekly on Tuesday, Thursday",
        "on":"on",
        "on_day":"on day",
        "on_the":"on the",
        "months":"months",
        "annually":"Annually",
        "years":"years",
        "once":"Once",
        "times":"times",
        "readmore":"read more",
        "readmore_less":"less",
        "readmore":"read more",
        "readmore_less":"less",
        "until":"until"
    }
});
