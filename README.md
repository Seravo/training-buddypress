An experimental WordPress plugin for adding Trainings to the Events Manager and Buddypress plugins.

Requires Events Manager and Buddypress.

Use event category "trainings" for training events, role "trainings" for training users and Buddypress groups for training organizations.

Add Trainings to a page with shortcodes like (change category number to whatever trainings is in your taxonomy)

[tm_trainings_tags]
[events_list limit="10" category="186"]
[events_calendar category="186"]
[locations_map category="186"]

Create normal Events pages without Trainings with shortcodes like (change category number to whatever trainings is in your taxonomy)

[events_list limit="10" category="-186"]
[events_calendar category="-186"]
[locations_map category="-186"]
