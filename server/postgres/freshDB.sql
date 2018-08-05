SET search_path TO newsite;
TRUNCATE cms_comments;                ALTER SEQUENCE cms_comments_cmnt_id_seq RESTART 1;
TRUNCATE cms_galeries;				      	ALTER SEQUENCE adv_galeries_id_glr_seq RESTART 1;
TRUNCATE cms_gallery_photos;	      	ALTER SEQUENCE adv_gallery_photos_id_cgp_seq RESTART 3;
TRUNCATE cms_gallery_sec;
TRUNCATE cms_gb;								      ALTER SEQUENCE cms_guestbook_id_gb_seq RESTART 1;
TRUNCATE cms_gb_tags;					      	ALTER SEQUENCE cms_gb_tags_gbt_id_seq RESTART 1;
TRUNCATE cms_gb_tags_gb;
TRUNCATE cms_menu_items;				      ALTER SEQUENCE cms_menu_items_mnui_id_seq RESTART 1;
TRUNCATE cms_news;						      	ALTER SEQUENCE cms_news_news_id_seq RESTART 1;
TRUNCATE cms_obj_photos;				      ALTER SEQUENCE cms_obj_photos_id_cop_seq RESTART 1;

DELETE FROM cms_sections where section_id>=9;	ALTER SEQUENCE cms_sections_section_id_seq RESTART 10;
UPDATE cms_sections SET sec_from = now();

TRUNCATE cms_sections_string;			      ALTER SEQUENCE cms_section_string_secs_id_seq RESTART 1;
TRUNCATE cms_srchwords;						      ALTER SEQUENCE adv_srchwords_id_srchw_seq RESTART 1;
TRUNCATE cms_srchwords_news;
TRUNCATE cms_srchwords_sections;
TRUNCATE cms_tags;								      ALTER SEQUENCE cms_tags_tag_id_seq RESTART 1;
TRUNCATE cms_tags_sections;

DELETE FROM cms_users where id_usr>=9;  ALTER SEQUENCE adv_users_id_usr_seq RESTART 10;
UPDATE cms_users SET usr_password_md5 = md5('dU%f:' || set_config('myapp.psw', md5(random()::text), true)) WHERE id_usr<10;
SELECT array_to_string(array(SELECT usr_login FROM cms_users),',') || ':' || current_setting('myapp.psw');