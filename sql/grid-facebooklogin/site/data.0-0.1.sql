-- insert default values for table: module

INSERT INTO "module" ( "module", "enabled" )
     VALUES ( 'Grid\FacebookLogin', FALSE );

-- update default values for table: user_right

DO LANGUAGE plpgsql $$
BEGIN

    IF NOT EXISTS ( SELECT *
                      FROM "user_right"
                     WHERE "group"      = 'settings'
                       AND "resource"   = 'settings.facebook'
                       AND "privilege"  = 'edit' ) THEN

        INSERT INTO "user_right" ( "label", "group", "resource", "privilege", "optional", "module" )
             VALUES ( NULL, 'settings', 'settings.facebook', 'edit', TRUE, 'Grid\FacebookLogin' );

    ELSE

        UPDATE "user_right"
           SET "module"     = "_common"."string_set_append"( "module", '|', 'Grid\FacebookLogin' )
         WHERE "group"      = 'settings'
           AND "resource"   = 'settings.facebook'
           AND "privilege"  = 'edit';

    END IF;

END $$;
