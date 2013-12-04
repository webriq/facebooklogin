-- remove data

DELETE FROM "module"
      WHERE "module" = 'Grid\FacebookLogin';

UPDATE "user_right"
   SET "module"     = "_common"."string_set_remove"( "module", '|', 'Grid\FacebookLogin' )
 WHERE "group"      = 'settings'
   AND "resource"   = 'settings.facebook'
   AND "privilege"  = 'edit';
