-- insert default values for table: module

INSERT INTO "module" ( "module", "enabled" )
     VALUES ( 'Grid\FacebookLogin', TRUE );

-- insert default values for table: user_right

INSERT INTO "user_right" ( "label", "group", "resource", "privilege", "optional", "module" )
     VALUES ( NULL, 'settings', 'settings.facebook', 'edit', TRUE, 'Grid\FacebookLogin' );
