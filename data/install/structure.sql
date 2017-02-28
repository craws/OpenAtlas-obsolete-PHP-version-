--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.5
-- Dumped by pg_dump version 9.5.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

-- CREATE EXTENSION postgis;

SET search_path = web, pg_catalog;

ALTER TABLE IF EXISTS ONLY web.user_settings DROP CONSTRAINT IF EXISTS user_settings_user_id_fkey;
ALTER TABLE IF EXISTS ONLY web."user" DROP CONSTRAINT IF EXISTS user_group_id_fkey;
ALTER TABLE IF EXISTS ONLY web.user_bookmarks DROP CONSTRAINT IF EXISTS user_bookmarks_user_id_fkey;
ALTER TABLE IF EXISTS ONLY web.user_bookmarks DROP CONSTRAINT IF EXISTS user_bookmarks_entity_id_fkey;
ALTER TABLE IF EXISTS ONLY web.i18n DROP CONSTRAINT IF EXISTS i18n_language_id_fkey;
ALTER TABLE IF EXISTS ONLY web.i18n DROP CONSTRAINT IF EXISTS i18n_item_id_fkey;
ALTER TABLE IF EXISTS ONLY web.hierarchy DROP CONSTRAINT IF EXISTS hierarchy_id_fkey;
ALTER TABLE IF EXISTS ONLY web.hierarchy_form DROP CONSTRAINT IF EXISTS hierarchy_form_hierarchy_id_fkey;
ALTER TABLE IF EXISTS ONLY web.hierarchy_form DROP CONSTRAINT IF EXISTS hierarchy_form_form_id_fkey;
SET search_path = model, pg_catalog;

ALTER TABLE IF EXISTS ONLY model.property DROP CONSTRAINT IF EXISTS property_range_class_id_fkey;
ALTER TABLE IF EXISTS ONLY model.property_inheritance DROP CONSTRAINT IF EXISTS property_inheritance_super_id_fkey;
ALTER TABLE IF EXISTS ONLY model.property_inheritance DROP CONSTRAINT IF EXISTS property_inheritance_sub_id_fkey;
ALTER TABLE IF EXISTS ONLY model.property DROP CONSTRAINT IF EXISTS property_domain_class_id_fkey;
ALTER TABLE IF EXISTS ONLY model.link DROP CONSTRAINT IF EXISTS link_range_id_fkey;
ALTER TABLE IF EXISTS ONLY model.link_property DROP CONSTRAINT IF EXISTS link_property_range_id_fkey;
ALTER TABLE IF EXISTS ONLY model.link_property DROP CONSTRAINT IF EXISTS link_property_property_id_fkey;
ALTER TABLE IF EXISTS ONLY model.link DROP CONSTRAINT IF EXISTS link_property_id_fkey;
ALTER TABLE IF EXISTS ONLY model.link_property DROP CONSTRAINT IF EXISTS link_property_domain_id_fkey;
ALTER TABLE IF EXISTS ONLY model.link DROP CONSTRAINT IF EXISTS link_domain_id_fkey;
ALTER TABLE IF EXISTS ONLY model.entity DROP CONSTRAINT IF EXISTS entity_class_id_fkey;
ALTER TABLE IF EXISTS ONLY model.class_inheritance DROP CONSTRAINT IF EXISTS class_inheritance_super_id_fkey;
ALTER TABLE IF EXISTS ONLY model.class_inheritance DROP CONSTRAINT IF EXISTS class_inheritance_sub_id_fkey;
SET search_path = log, pg_catalog;

ALTER TABLE IF EXISTS ONLY log.detail DROP CONSTRAINT IF EXISTS detail_log_id_fkey;
SET search_path = gis, pg_catalog;

ALTER TABLE IF EXISTS ONLY gis.polygon DROP CONSTRAINT IF EXISTS polygon_entity_id_fkey;
ALTER TABLE IF EXISTS ONLY gis.point DROP CONSTRAINT IF EXISTS point_entity_id_fkey;
ALTER TABLE IF EXISTS ONLY gis.linestring DROP CONSTRAINT IF EXISTS linestring_entity_id_fkey;
SET search_path = web, pg_catalog;

DROP TRIGGER IF EXISTS update_modified ON web.hierarchy_form;
DROP TRIGGER IF EXISTS update_modified ON web.form;
DROP TRIGGER IF EXISTS update_modified ON web.hierarchy;
DROP TRIGGER IF EXISTS update_modified ON web.user_bookmarks;
DROP TRIGGER IF EXISTS update_modified ON web.user_settings;
DROP TRIGGER IF EXISTS update_modified ON web.content;
DROP TRIGGER IF EXISTS update_modified ON web.language;
DROP TRIGGER IF EXISTS update_modified ON web.i18n;
DROP TRIGGER IF EXISTS update_modified ON web."group";
DROP TRIGGER IF EXISTS update_modified ON web."user";
SET search_path = model, pg_catalog;

DROP TRIGGER IF EXISTS update_modified ON model.link_property;
DROP TRIGGER IF EXISTS update_modified ON model.property_inheritance;
DROP TRIGGER IF EXISTS update_modified ON model.link;
DROP TRIGGER IF EXISTS update_modified ON model.entity;
DROP TRIGGER IF EXISTS update_modified ON model.property;
DROP TRIGGER IF EXISTS update_modified ON model.i18n;
DROP TRIGGER IF EXISTS update_modified ON model.class_inheritance;
DROP TRIGGER IF EXISTS update_modified ON model.class;
SET search_path = gis, pg_catalog;

DROP TRIGGER IF EXISTS update_modified ON gis.polygon;
DROP TRIGGER IF EXISTS update_modified ON gis.linestring;
DROP TRIGGER IF EXISTS update_modified ON gis.point;
SET search_path = web, pg_catalog;

ALTER TABLE IF EXISTS ONLY web."user" DROP CONSTRAINT IF EXISTS user_username_key;
ALTER TABLE IF EXISTS ONLY web.user_settings DROP CONSTRAINT IF EXISTS user_settings_user_id_name_value_key;
ALTER TABLE IF EXISTS ONLY web.user_settings DROP CONSTRAINT IF EXISTS user_settings_pkey;
ALTER TABLE IF EXISTS ONLY web."user" DROP CONSTRAINT IF EXISTS user_pkey;
ALTER TABLE IF EXISTS ONLY web.user_log DROP CONSTRAINT IF EXISTS user_log_pkey;
ALTER TABLE IF EXISTS ONLY web."user" DROP CONSTRAINT IF EXISTS user_email_key;
ALTER TABLE IF EXISTS ONLY web.user_bookmarks DROP CONSTRAINT IF EXISTS user_bookmarks_user_id_entity_id_key;
ALTER TABLE IF EXISTS ONLY web.user_bookmarks DROP CONSTRAINT IF EXISTS user_bookmarks_pkey;
ALTER TABLE IF EXISTS ONLY web."user" DROP CONSTRAINT IF EXISTS unsubscribe_code_key;
ALTER TABLE IF EXISTS ONLY web.settings DROP CONSTRAINT IF EXISTS settings_pkey;
ALTER TABLE IF EXISTS ONLY web.settings DROP CONSTRAINT IF EXISTS settings_name_key;
ALTER TABLE IF EXISTS ONLY web.language DROP CONSTRAINT IF EXISTS language_shortform_key;
ALTER TABLE IF EXISTS ONLY web.language DROP CONSTRAINT IF EXISTS language_pkey;
ALTER TABLE IF EXISTS ONLY web.language DROP CONSTRAINT IF EXISTS language_name_key;
ALTER TABLE IF EXISTS ONLY web.i18n DROP CONSTRAINT IF EXISTS i18n_pkey;
ALTER TABLE IF EXISTS ONLY web.i18n DROP CONSTRAINT IF EXISTS i18n_field_foreign_id_language_id_key;
ALTER TABLE IF EXISTS ONLY web.hierarchy DROP CONSTRAINT IF EXISTS hierarchy_pkey;
ALTER TABLE IF EXISTS ONLY web.hierarchy_form DROP CONSTRAINT IF EXISTS hierarchy_form_pkey;
ALTER TABLE IF EXISTS ONLY web."group" DROP CONSTRAINT IF EXISTS group_pkey;
ALTER TABLE IF EXISTS ONLY web.form DROP CONSTRAINT IF EXISTS form_pkey;
ALTER TABLE IF EXISTS ONLY web.form DROP CONSTRAINT IF EXISTS form_name_key;
ALTER TABLE IF EXISTS ONLY web.content DROP CONSTRAINT IF EXISTS content_pkey;
SET search_path = model, pg_catalog;

ALTER TABLE IF EXISTS ONLY model.property DROP CONSTRAINT IF EXISTS property_pkey;
ALTER TABLE IF EXISTS ONLY model.property_inheritance DROP CONSTRAINT IF EXISTS property_inheritance_pkey;
ALTER TABLE IF EXISTS ONLY model.property DROP CONSTRAINT IF EXISTS property_code_key;
ALTER TABLE IF EXISTS ONLY model.link_property DROP CONSTRAINT IF EXISTS link_property_pkey;
ALTER TABLE IF EXISTS ONLY model.link DROP CONSTRAINT IF EXISTS link_pkey;
ALTER TABLE IF EXISTS ONLY model.i18n DROP CONSTRAINT IF EXISTS i18n_table_name_table_field_table_id_language_code_key;
ALTER TABLE IF EXISTS ONLY model.i18n DROP CONSTRAINT IF EXISTS i18n_pkey;
ALTER TABLE IF EXISTS ONLY model.entity DROP CONSTRAINT IF EXISTS entity_pkey;
ALTER TABLE IF EXISTS ONLY model.class DROP CONSTRAINT IF EXISTS class_pkey;
ALTER TABLE IF EXISTS ONLY model.class DROP CONSTRAINT IF EXISTS class_name_key;
ALTER TABLE IF EXISTS ONLY model.class_inheritance DROP CONSTRAINT IF EXISTS class_inheritance_super_id_sub_id_key;
ALTER TABLE IF EXISTS ONLY model.class_inheritance DROP CONSTRAINT IF EXISTS class_inheritance_pkey;
ALTER TABLE IF EXISTS ONLY model.class DROP CONSTRAINT IF EXISTS class_code_key;
SET search_path = log, pg_catalog;

ALTER TABLE IF EXISTS ONLY log.log DROP CONSTRAINT IF EXISTS log_pkey;
ALTER TABLE IF EXISTS ONLY log.detail DROP CONSTRAINT IF EXISTS log_detail_pkey;
SET search_path = gis, pg_catalog;

ALTER TABLE IF EXISTS ONLY gis.polygon DROP CONSTRAINT IF EXISTS polygon_pkey;
ALTER TABLE IF EXISTS ONLY gis.point DROP CONSTRAINT IF EXISTS point_pkey;
ALTER TABLE IF EXISTS ONLY gis.linestring DROP CONSTRAINT IF EXISTS linestring_pkey;
SET search_path = web, pg_catalog;

ALTER TABLE IF EXISTS web.user_settings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.user_log ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.user_bookmarks ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web."user" ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.settings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.language ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.i18n ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.hierarchy_form ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.hierarchy ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web."group" ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.form ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS web.content ALTER COLUMN id DROP DEFAULT;
SET search_path = model, pg_catalog;

ALTER TABLE IF EXISTS model.property_inheritance ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.property ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.link_property ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.link ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.i18n ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.entity ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.class_inheritance ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS model.class ALTER COLUMN id DROP DEFAULT;
SET search_path = log, pg_catalog;

ALTER TABLE IF EXISTS log.log ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS log.detail ALTER COLUMN id DROP DEFAULT;
SET search_path = gis, pg_catalog;

ALTER TABLE IF EXISTS gis.polygon ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS gis.point ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS gis.linestring ALTER COLUMN id DROP DEFAULT;
SET search_path = web, pg_catalog;

DROP SEQUENCE IF EXISTS web.user_settings_id_seq;
DROP TABLE IF EXISTS web.user_settings;
DROP SEQUENCE IF EXISTS web.user_log_id_seq;
DROP TABLE IF EXISTS web.user_log;
DROP SEQUENCE IF EXISTS web.user_id_seq;
DROP SEQUENCE IF EXISTS web.user_bookmarks_id_seq;
DROP TABLE IF EXISTS web.user_bookmarks;
DROP TABLE IF EXISTS web."user";
DROP SEQUENCE IF EXISTS web.settings_id_seq;
DROP TABLE IF EXISTS web.settings;
DROP SEQUENCE IF EXISTS web.language_id_seq;
DROP TABLE IF EXISTS web.language;
DROP SEQUENCE IF EXISTS web.item_id_seq;
DROP SEQUENCE IF EXISTS web.i18n_id_seq;
DROP TABLE IF EXISTS web.i18n;
DROP SEQUENCE IF EXISTS web.hierarchy_id_seq;
DROP SEQUENCE IF EXISTS web.hierarchy_form_id_seq;
DROP TABLE IF EXISTS web.hierarchy_form;
DROP TABLE IF EXISTS web.hierarchy;
DROP SEQUENCE IF EXISTS web.group_id_seq;
DROP TABLE IF EXISTS web."group";
DROP SEQUENCE IF EXISTS web.form_id_seq;
DROP TABLE IF EXISTS web.form;
DROP TABLE IF EXISTS web.content;
SET search_path = model, pg_catalog;

DROP SEQUENCE IF EXISTS model.property_inheritance_id_seq;
DROP TABLE IF EXISTS model.property_inheritance;
DROP SEQUENCE IF EXISTS model.property_id_seq;
DROP TABLE IF EXISTS model.property;
DROP SEQUENCE IF EXISTS model.link_property_id_seq;
DROP TABLE IF EXISTS model.link_property;
DROP SEQUENCE IF EXISTS model.link_id_seq;
DROP TABLE IF EXISTS model.link;
DROP SEQUENCE IF EXISTS model.i18n_id_seq;
DROP TABLE IF EXISTS model.i18n;
DROP SEQUENCE IF EXISTS model.entity_id_seq;
DROP TABLE IF EXISTS model.entity;
DROP SEQUENCE IF EXISTS model.class_inheritance_id_seq;
DROP TABLE IF EXISTS model.class_inheritance;
DROP SEQUENCE IF EXISTS model.class_id_seq;
DROP TABLE IF EXISTS model.class;
SET search_path = log, pg_catalog;

DROP SEQUENCE IF EXISTS log.log_id_seq;
DROP TABLE IF EXISTS log.log;
DROP SEQUENCE IF EXISTS log.detail_id_seq;
DROP TABLE IF EXISTS log.detail;
SET search_path = gis, pg_catalog;

DROP SEQUENCE IF EXISTS gis.polygon_id_seq;
DROP TABLE IF EXISTS gis.polygon;
DROP SEQUENCE IF EXISTS gis.point_id_seq;
DROP TABLE IF EXISTS gis.point;
DROP SEQUENCE IF EXISTS gis.linestring_id_seq;
DROP TABLE IF EXISTS gis.linestring;
SET search_path = model, pg_catalog;

DROP FUNCTION IF EXISTS model.update_modified();
SET search_path = gis, pg_catalog;

DROP FUNCTION IF EXISTS gis.geometry_creation();
DROP SCHEMA IF EXISTS web;
DROP SCHEMA IF EXISTS model;
DROP SCHEMA IF EXISTS log;
DROP SCHEMA IF EXISTS gis;
--
-- Name: gis; Type: SCHEMA; Schema: -; Owner: openatlas_master
--

CREATE SCHEMA gis;


ALTER SCHEMA gis OWNER TO openatlas_master;

--
-- Name: log; Type: SCHEMA; Schema: -; Owner: openatlas_master
--

CREATE SCHEMA log;


ALTER SCHEMA log OWNER TO openatlas_master;

--
-- Name: model; Type: SCHEMA; Schema: -; Owner: openatlas_master
--

CREATE SCHEMA model;


ALTER SCHEMA model OWNER TO openatlas_master;

--
-- Name: web; Type: SCHEMA; Schema: -; Owner: openatlas_master
--

CREATE SCHEMA web;


ALTER SCHEMA web OWNER TO openatlas_master;

SET search_path = gis, pg_catalog;

--
-- Name: geometry_creation(); Type: FUNCTION; Schema: gis; Owner: openatlas_master
--

CREATE FUNCTION geometry_creation() RETURNS trigger
    LANGUAGE plpgsql
    AS $$  BEGIN
   IF (NEW.geom IS NULL) THEN
    NEW.geom = ST_SetSRID(ST_MakePoint(NEW.easting, NEW.northing), 4326);
   END IF;

   IF (NEW.easting IS NULL) THEN
    NEW.easting = ST_X(NEW.geom);
    NEW.northing = ST_Y(NEW.geom);
   END IF;

   IF (NEW.northing IS NULL) THEN
    NEW.easting = ST_X(NEW.geom);
    NEW.northing = ST_Y(NEW.geom);
   END IF;

    NEW.easting = ST_X(NEW.geom);
    NEW.northing = ST_Y(NEW.geom);


   RETURN NEW;
  END;
$$;


ALTER FUNCTION gis.geometry_creation() OWNER TO openatlas_master;

SET search_path = model, pg_catalog;

--
-- Name: update_modified(); Type: FUNCTION; Schema: model; Owner: openatlas_master
--

CREATE FUNCTION update_modified() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

   NEW.modified = now();

   RETURN NEW;

END;

$$;


ALTER FUNCTION model.update_modified() OWNER TO openatlas_master;

SET search_path = gis, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: linestring; Type: TABLE; Schema: gis; Owner: openatlas_master
--

CREATE TABLE linestring (
    id integer NOT NULL,
    entity_id integer NOT NULL,
    name text,
    description text,
    type text,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone,
    geom public.geometry(LineString,4326)
);


ALTER TABLE linestring OWNER TO openatlas_master;

--
-- Name: linestring_id_seq; Type: SEQUENCE; Schema: gis; Owner: openatlas_master
--

CREATE SEQUENCE linestring_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE linestring_id_seq OWNER TO openatlas_master;

--
-- Name: linestring_id_seq; Type: SEQUENCE OWNED BY; Schema: gis; Owner: openatlas_master
--

ALTER SEQUENCE linestring_id_seq OWNED BY linestring.id;


--
-- Name: point; Type: TABLE; Schema: gis; Owner: openatlas_master
--

CREATE TABLE point (
    id integer NOT NULL,
    entity_id integer NOT NULL,
    name text,
    description text,
    type text,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone,
    geom public.geometry(Point,4326)
);


ALTER TABLE point OWNER TO openatlas_master;

--
-- Name: point_id_seq; Type: SEQUENCE; Schema: gis; Owner: openatlas_master
--

CREATE SEQUENCE point_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE point_id_seq OWNER TO openatlas_master;

--
-- Name: point_id_seq; Type: SEQUENCE OWNED BY; Schema: gis; Owner: openatlas_master
--

ALTER SEQUENCE point_id_seq OWNED BY point.id;


--
-- Name: polygon; Type: TABLE; Schema: gis; Owner: openatlas_master
--

CREATE TABLE polygon (
    id integer NOT NULL,
    entity_id integer NOT NULL,
    name text,
    description text,
    type text,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone,
    geom public.geometry(Polygon,4326)
);


ALTER TABLE polygon OWNER TO openatlas_master;

--
-- Name: polygon_id_seq; Type: SEQUENCE; Schema: gis; Owner: openatlas_master
--

CREATE SEQUENCE polygon_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE polygon_id_seq OWNER TO openatlas_master;

--
-- Name: polygon_id_seq; Type: SEQUENCE OWNED BY; Schema: gis; Owner: openatlas_master
--

ALTER SEQUENCE polygon_id_seq OWNED BY polygon.id;


SET search_path = log, pg_catalog;

--
-- Name: detail; Type: TABLE; Schema: log; Owner: openatlas_master
--

CREATE TABLE detail (
    id integer NOT NULL,
    log_id integer NOT NULL,
    key text NOT NULL,
    value text NOT NULL
);


ALTER TABLE detail OWNER TO openatlas_master;

--
-- Name: detail_id_seq; Type: SEQUENCE; Schema: log; Owner: openatlas_master
--

CREATE SEQUENCE detail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE detail_id_seq OWNER TO openatlas_master;

--
-- Name: detail_id_seq; Type: SEQUENCE OWNED BY; Schema: log; Owner: openatlas_master
--

ALTER SEQUENCE detail_id_seq OWNED BY detail.id;


--
-- Name: log; Type: TABLE; Schema: log; Owner: openatlas_master
--

CREATE TABLE log (
    id integer NOT NULL,
    priority integer NOT NULL,
    type text,
    message text NOT NULL,
    user_id integer,
    ip text,
    agent text,
    created timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE log OWNER TO openatlas_master;

--
-- Name: log_id_seq; Type: SEQUENCE; Schema: log; Owner: openatlas_master
--

CREATE SEQUENCE log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE log_id_seq OWNER TO openatlas_master;

--
-- Name: log_id_seq; Type: SEQUENCE OWNED BY; Schema: log; Owner: openatlas_master
--

ALTER SEQUENCE log_id_seq OWNED BY log.id;


SET search_path = model, pg_catalog;

--
-- Name: class; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE class (
    id integer NOT NULL,
    code text NOT NULL,
    name text NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE class OWNER TO openatlas_master;

--
-- Name: COLUMN class.code; Type: COMMENT; Schema: model; Owner: openatlas_master
--

COMMENT ON COLUMN class.code IS 'e.g. E21';


--
-- Name: COLUMN class.name; Type: COMMENT; Schema: model; Owner: openatlas_master
--

COMMENT ON COLUMN class.name IS 'e.g. Person';


--
-- Name: class_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE class_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE class_id_seq OWNER TO openatlas_master;

--
-- Name: class_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE class_id_seq OWNED BY class.id;


--
-- Name: class_inheritance; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE class_inheritance (
    id integer NOT NULL,
    super_id integer NOT NULL,
    sub_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modfied timestamp without time zone
);


ALTER TABLE class_inheritance OWNER TO openatlas_master;

--
-- Name: class_inheritance_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE class_inheritance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE class_inheritance_id_seq OWNER TO openatlas_master;

--
-- Name: class_inheritance_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE class_inheritance_id_seq OWNED BY class_inheritance.id;


--
-- Name: entity; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE entity (
    id integer NOT NULL,
    class_id integer NOT NULL,
    name text NOT NULL,
    description text,
    value_integer integer,
    value_timestamp timestamp without time zone,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE entity OWNER TO openatlas_master;

--
-- Name: entity_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE entity_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE entity_id_seq OWNER TO openatlas_master;

--
-- Name: entity_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE entity_id_seq OWNED BY entity.id;


--
-- Name: i18n; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE i18n (
    id integer NOT NULL,
    table_name text NOT NULL,
    table_field text NOT NULL,
    table_id integer NOT NULL,
    language_code text NOT NULL,
    text text NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE i18n OWNER TO openatlas_master;

--
-- Name: i18n_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE i18n_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE i18n_id_seq OWNER TO openatlas_master;

--
-- Name: i18n_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE i18n_id_seq OWNED BY i18n.id;


--
-- Name: link; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE link (
    id integer NOT NULL,
    property_id integer NOT NULL,
    domain_id integer NOT NULL,
    range_id integer NOT NULL,
    description text,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE link OWNER TO openatlas_master;

--
-- Name: link_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE link_id_seq OWNER TO openatlas_master;

--
-- Name: link_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE link_id_seq OWNED BY link.id;


--
-- Name: link_property; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE link_property (
    id integer NOT NULL,
    property_id integer NOT NULL,
    domain_id integer NOT NULL,
    range_id integer NOT NULL,
    description text,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE link_property OWNER TO openatlas_master;

--
-- Name: link_property_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE link_property_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE link_property_id_seq OWNER TO openatlas_master;

--
-- Name: link_property_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE link_property_id_seq OWNED BY link_property.id;


--
-- Name: property; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE property (
    id integer NOT NULL,
    code text NOT NULL,
    range_class_id integer NOT NULL,
    domain_class_id integer NOT NULL,
    name text NOT NULL,
    name_inverse text,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE property OWNER TO openatlas_master;

--
-- Name: property_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE property_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE property_id_seq OWNER TO openatlas_master;

--
-- Name: property_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE property_id_seq OWNED BY property.id;


SET default_with_oids = true;

--
-- Name: property_inheritance; Type: TABLE; Schema: model; Owner: openatlas_master
--

CREATE TABLE property_inheritance (
    id integer NOT NULL,
    super_id integer NOT NULL,
    sub_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE property_inheritance OWNER TO openatlas_master;

--
-- Name: property_inheritance_id_seq; Type: SEQUENCE; Schema: model; Owner: openatlas_master
--

CREATE SEQUENCE property_inheritance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE property_inheritance_id_seq OWNER TO openatlas_master;

--
-- Name: property_inheritance_id_seq; Type: SEQUENCE OWNED BY; Schema: model; Owner: openatlas_master
--

ALTER SEQUENCE property_inheritance_id_seq OWNED BY property_inheritance.id;


SET search_path = web, pg_catalog;

SET default_with_oids = false;

--
-- Name: content; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE content (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE content OWNER TO openatlas_master;

--
-- Name: form; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE form (
    id integer NOT NULL,
    name text NOT NULL,
    extendable integer DEFAULT 0 NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE form OWNER TO openatlas_master;

--
-- Name: form_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE form_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE form_id_seq OWNER TO openatlas_master;

--
-- Name: form_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE form_id_seq OWNED BY form.id;


--
-- Name: group; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE "group" (
    id integer NOT NULL,
    name text NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE "group" OWNER TO openatlas_master;

--
-- Name: group_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE group_id_seq OWNER TO openatlas_master;

--
-- Name: group_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE group_id_seq OWNED BY "group".id;


--
-- Name: hierarchy; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE hierarchy (
    id integer NOT NULL,
    name text NOT NULL,
    multiple integer DEFAULT 0 NOT NULL,
    system integer DEFAULT 0 NOT NULL,
    extendable integer DEFAULT 0 NOT NULL,
    directional integer DEFAULT 0 NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE hierarchy OWNER TO openatlas_master;

--
-- Name: COLUMN hierarchy.id; Type: COMMENT; Schema: web; Owner: openatlas_master
--

COMMENT ON COLUMN hierarchy.id IS 'same as model.entity.id';


--
-- Name: COLUMN hierarchy.name; Type: COMMENT; Schema: web; Owner: openatlas_master
--

COMMENT ON COLUMN hierarchy.name IS 'same as model.entity.name, to ensure unique root type names';


--
-- Name: hierarchy_form; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE hierarchy_form (
    id integer NOT NULL,
    hierarchy_id integer NOT NULL,
    form_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE hierarchy_form OWNER TO openatlas_master;

--
-- Name: hierarchy_form_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE hierarchy_form_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE hierarchy_form_id_seq OWNER TO openatlas_master;

--
-- Name: hierarchy_form_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE hierarchy_form_id_seq OWNED BY hierarchy_form.id;


--
-- Name: hierarchy_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE hierarchy_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE hierarchy_id_seq OWNER TO openatlas_master;

--
-- Name: hierarchy_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE hierarchy_id_seq OWNED BY hierarchy.id;


--
-- Name: i18n; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE i18n (
    id integer NOT NULL,
    field text NOT NULL,
    text text DEFAULT ''::text NOT NULL,
    item_id integer NOT NULL,
    language_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp with time zone
);


ALTER TABLE i18n OWNER TO openatlas_master;

--
-- Name: COLUMN i18n.field; Type: COMMENT; Schema: web; Owner: openatlas_master
--

COMMENT ON COLUMN i18n.field IS 'field names (for eg. news) are hardcoded in source code';


--
-- Name: i18n_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE i18n_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE i18n_id_seq OWNER TO openatlas_master;

--
-- Name: i18n_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE i18n_id_seq OWNED BY i18n.id;


--
-- Name: item_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE item_id_seq OWNER TO openatlas_master;

--
-- Name: item_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE item_id_seq OWNED BY content.id;


--
-- Name: language; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE language (
    id integer NOT NULL,
    active integer DEFAULT 0 NOT NULL,
    name text NOT NULL,
    shortform text NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE language OWNER TO openatlas_master;

--
-- Name: language_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE language_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE language_id_seq OWNER TO openatlas_master;

--
-- Name: language_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE language_id_seq OWNED BY language.id;


--
-- Name: settings; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE settings (
    id integer NOT NULL,
    name text NOT NULL,
    value text NOT NULL
);


ALTER TABLE settings OWNER TO openatlas_master;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE settings_id_seq OWNER TO openatlas_master;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE settings_id_seq OWNED BY settings.id;


--
-- Name: user; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE "user" (
    id integer NOT NULL,
    group_id integer NOT NULL,
    username text NOT NULL,
    password text NOT NULL,
    active integer DEFAULT 0 NOT NULL,
    real_name text DEFAULT ''::text NOT NULL,
    email text,
    info text DEFAULT ''::text NOT NULL,
    login_last_success timestamp without time zone,
    login_last_failure timestamp without time zone,
    login_failed_count integer DEFAULT 0 NOT NULL,
    password_reset_code text,
    password_reset_date timestamp without time zone,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone,
    unsubscribe_code text
);


ALTER TABLE "user" OWNER TO openatlas_master;

--
-- Name: user_bookmarks; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE user_bookmarks (
    id integer NOT NULL,
    user_id integer NOT NULL,
    entity_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE user_bookmarks OWNER TO openatlas_master;

--
-- Name: user_bookmarks_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE user_bookmarks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_bookmarks_id_seq OWNER TO openatlas_master;

--
-- Name: user_bookmarks_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE user_bookmarks_id_seq OWNED BY user_bookmarks.id;


--
-- Name: user_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_id_seq OWNER TO openatlas_master;

--
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE user_id_seq OWNED BY "user".id;


--
-- Name: user_log; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE user_log (
    id integer NOT NULL,
    user_id integer NOT NULL,
    table_name text NOT NULL,
    table_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    action text NOT NULL
);


ALTER TABLE user_log OWNER TO openatlas_master;

--
-- Name: user_log_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE user_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_log_id_seq OWNER TO openatlas_master;

--
-- Name: user_log_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE user_log_id_seq OWNED BY user_log.id;


--
-- Name: user_settings; Type: TABLE; Schema: web; Owner: openatlas_master
--

CREATE TABLE user_settings (
    id integer NOT NULL,
    user_id integer NOT NULL,
    name text NOT NULL,
    value text NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    modified timestamp without time zone
);


ALTER TABLE user_settings OWNER TO openatlas_master;

--
-- Name: user_settings_id_seq; Type: SEQUENCE; Schema: web; Owner: openatlas_master
--

CREATE SEQUENCE user_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_settings_id_seq OWNER TO openatlas_master;

--
-- Name: user_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: web; Owner: openatlas_master
--

ALTER SEQUENCE user_settings_id_seq OWNED BY user_settings.id;


SET search_path = gis, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY linestring ALTER COLUMN id SET DEFAULT nextval('linestring_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY point ALTER COLUMN id SET DEFAULT nextval('point_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY polygon ALTER COLUMN id SET DEFAULT nextval('polygon_id_seq'::regclass);


SET search_path = log, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: log; Owner: openatlas_master
--

ALTER TABLE ONLY detail ALTER COLUMN id SET DEFAULT nextval('detail_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: log; Owner: openatlas_master
--

ALTER TABLE ONLY log ALTER COLUMN id SET DEFAULT nextval('log_id_seq'::regclass);


SET search_path = model, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class ALTER COLUMN id SET DEFAULT nextval('class_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class_inheritance ALTER COLUMN id SET DEFAULT nextval('class_inheritance_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY entity ALTER COLUMN id SET DEFAULT nextval('entity_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY i18n ALTER COLUMN id SET DEFAULT nextval('i18n_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link ALTER COLUMN id SET DEFAULT nextval('link_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link_property ALTER COLUMN id SET DEFAULT nextval('link_property_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property ALTER COLUMN id SET DEFAULT nextval('property_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property_inheritance ALTER COLUMN id SET DEFAULT nextval('property_inheritance_id_seq'::regclass);


SET search_path = web, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY content ALTER COLUMN id SET DEFAULT nextval('item_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY form ALTER COLUMN id SET DEFAULT nextval('form_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "group" ALTER COLUMN id SET DEFAULT nextval('group_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy ALTER COLUMN id SET DEFAULT nextval('hierarchy_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy_form ALTER COLUMN id SET DEFAULT nextval('hierarchy_form_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY i18n ALTER COLUMN id SET DEFAULT nextval('i18n_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY language ALTER COLUMN id SET DEFAULT nextval('language_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY settings ALTER COLUMN id SET DEFAULT nextval('settings_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "user" ALTER COLUMN id SET DEFAULT nextval('user_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_bookmarks ALTER COLUMN id SET DEFAULT nextval('user_bookmarks_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_log ALTER COLUMN id SET DEFAULT nextval('user_log_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_settings ALTER COLUMN id SET DEFAULT nextval('user_settings_id_seq'::regclass);


SET search_path = gis, pg_catalog;

--
-- Name: linestring_pkey; Type: CONSTRAINT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY linestring
    ADD CONSTRAINT linestring_pkey PRIMARY KEY (id);


--
-- Name: point_pkey; Type: CONSTRAINT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY point
    ADD CONSTRAINT point_pkey PRIMARY KEY (id);


--
-- Name: polygon_pkey; Type: CONSTRAINT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY polygon
    ADD CONSTRAINT polygon_pkey PRIMARY KEY (id);


SET search_path = log, pg_catalog;

--
-- Name: log_detail_pkey; Type: CONSTRAINT; Schema: log; Owner: openatlas_master
--

ALTER TABLE ONLY detail
    ADD CONSTRAINT log_detail_pkey PRIMARY KEY (id);


--
-- Name: log_pkey; Type: CONSTRAINT; Schema: log; Owner: openatlas_master
--

ALTER TABLE ONLY log
    ADD CONSTRAINT log_pkey PRIMARY KEY (id);


SET search_path = model, pg_catalog;

--
-- Name: class_code_key; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class
    ADD CONSTRAINT class_code_key UNIQUE (code);


--
-- Name: class_inheritance_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class_inheritance
    ADD CONSTRAINT class_inheritance_pkey PRIMARY KEY (id);


--
-- Name: class_inheritance_super_id_sub_id_key; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class_inheritance
    ADD CONSTRAINT class_inheritance_super_id_sub_id_key UNIQUE (super_id, sub_id);


--
-- Name: class_name_key; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class
    ADD CONSTRAINT class_name_key UNIQUE (name);


--
-- Name: class_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class
    ADD CONSTRAINT class_pkey PRIMARY KEY (id);


--
-- Name: entity_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY entity
    ADD CONSTRAINT entity_pkey PRIMARY KEY (id);


--
-- Name: i18n_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY i18n
    ADD CONSTRAINT i18n_pkey PRIMARY KEY (id);


--
-- Name: i18n_table_name_table_field_table_id_language_code_key; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY i18n
    ADD CONSTRAINT i18n_table_name_table_field_table_id_language_code_key UNIQUE (table_name, table_field, table_id, language_code);


--
-- Name: link_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link
    ADD CONSTRAINT link_pkey PRIMARY KEY (id);


--
-- Name: link_property_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link_property
    ADD CONSTRAINT link_property_pkey PRIMARY KEY (id);


--
-- Name: property_code_key; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property
    ADD CONSTRAINT property_code_key UNIQUE (code);


--
-- Name: property_inheritance_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property_inheritance
    ADD CONSTRAINT property_inheritance_pkey PRIMARY KEY (id);


--
-- Name: property_pkey; Type: CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property
    ADD CONSTRAINT property_pkey PRIMARY KEY (id);


SET search_path = web, pg_catalog;

--
-- Name: content_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY content
    ADD CONSTRAINT content_pkey PRIMARY KEY (id);


--
-- Name: form_name_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY form
    ADD CONSTRAINT form_name_key UNIQUE (name);


--
-- Name: form_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY form
    ADD CONSTRAINT form_pkey PRIMARY KEY (id);


--
-- Name: group_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "group"
    ADD CONSTRAINT group_pkey PRIMARY KEY (id);


--
-- Name: hierarchy_form_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy_form
    ADD CONSTRAINT hierarchy_form_pkey PRIMARY KEY (id);


--
-- Name: hierarchy_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy
    ADD CONSTRAINT hierarchy_pkey PRIMARY KEY (id);


--
-- Name: i18n_field_foreign_id_language_id_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY i18n
    ADD CONSTRAINT i18n_field_foreign_id_language_id_key UNIQUE (field, item_id, language_id);


--
-- Name: i18n_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY i18n
    ADD CONSTRAINT i18n_pkey PRIMARY KEY (id);


--
-- Name: language_name_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY language
    ADD CONSTRAINT language_name_key UNIQUE (name);


--
-- Name: language_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY language
    ADD CONSTRAINT language_pkey PRIMARY KEY (id);


--
-- Name: language_shortform_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY language
    ADD CONSTRAINT language_shortform_key UNIQUE (shortform);


--
-- Name: settings_name_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY settings
    ADD CONSTRAINT settings_name_key UNIQUE (name);


--
-- Name: settings_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: unsubscribe_code_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT unsubscribe_code_key UNIQUE (unsubscribe_code);


--
-- Name: user_bookmarks_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_bookmarks
    ADD CONSTRAINT user_bookmarks_pkey PRIMARY KEY (id);


--
-- Name: user_bookmarks_user_id_entity_id_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_bookmarks
    ADD CONSTRAINT user_bookmarks_user_id_entity_id_key UNIQUE (user_id, entity_id);


--
-- Name: user_email_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_email_key UNIQUE (email);


--
-- Name: user_log_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_log
    ADD CONSTRAINT user_log_pkey PRIMARY KEY (id);


--
-- Name: user_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: user_settings_pkey; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_settings
    ADD CONSTRAINT user_settings_pkey PRIMARY KEY (id);


--
-- Name: user_settings_user_id_name_value_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_settings
    ADD CONSTRAINT user_settings_user_id_name_value_key UNIQUE (user_id, name, value);


--
-- Name: user_username_key; Type: CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_username_key UNIQUE (username);


SET search_path = gis, pg_catalog;

--
-- Name: update_modified; Type: TRIGGER; Schema: gis; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON point FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: gis; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON linestring FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: gis; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON polygon FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


SET search_path = model, pg_catalog;

--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON class FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON class_inheritance FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON i18n FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON property FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON entity FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON link FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON property_inheritance FOR EACH ROW EXECUTE PROCEDURE update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: model; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON link_property FOR EACH ROW EXECUTE PROCEDURE update_modified();


SET search_path = web, pg_catalog;

--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON "user" FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON "group" FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON i18n FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON language FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON content FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON user_settings FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON user_bookmarks FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON hierarchy FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON form FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


--
-- Name: update_modified; Type: TRIGGER; Schema: web; Owner: openatlas_master
--

CREATE TRIGGER update_modified BEFORE UPDATE ON hierarchy_form FOR EACH ROW EXECUTE PROCEDURE model.update_modified();


SET search_path = gis, pg_catalog;

--
-- Name: linestring_entity_id_fkey; Type: FK CONSTRAINT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY linestring
    ADD CONSTRAINT linestring_entity_id_fkey FOREIGN KEY (entity_id) REFERENCES model.entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: point_entity_id_fkey; Type: FK CONSTRAINT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY point
    ADD CONSTRAINT point_entity_id_fkey FOREIGN KEY (entity_id) REFERENCES model.entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: polygon_entity_id_fkey; Type: FK CONSTRAINT; Schema: gis; Owner: openatlas_master
--

ALTER TABLE ONLY polygon
    ADD CONSTRAINT polygon_entity_id_fkey FOREIGN KEY (entity_id) REFERENCES model.entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = log, pg_catalog;

--
-- Name: detail_log_id_fkey; Type: FK CONSTRAINT; Schema: log; Owner: openatlas_master
--

ALTER TABLE ONLY detail
    ADD CONSTRAINT detail_log_id_fkey FOREIGN KEY (log_id) REFERENCES log(id) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = model, pg_catalog;

--
-- Name: class_inheritance_sub_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class_inheritance
    ADD CONSTRAINT class_inheritance_sub_id_fkey FOREIGN KEY (sub_id) REFERENCES class(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: class_inheritance_super_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY class_inheritance
    ADD CONSTRAINT class_inheritance_super_id_fkey FOREIGN KEY (super_id) REFERENCES class(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entity_class_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY entity
    ADD CONSTRAINT entity_class_id_fkey FOREIGN KEY (class_id) REFERENCES class(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: link_domain_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link
    ADD CONSTRAINT link_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: link_property_domain_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link_property
    ADD CONSTRAINT link_property_domain_id_fkey FOREIGN KEY (domain_id) REFERENCES link(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: link_property_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link
    ADD CONSTRAINT link_property_id_fkey FOREIGN KEY (property_id) REFERENCES property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: link_property_property_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link_property
    ADD CONSTRAINT link_property_property_id_fkey FOREIGN KEY (property_id) REFERENCES property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: link_property_range_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link_property
    ADD CONSTRAINT link_property_range_id_fkey FOREIGN KEY (range_id) REFERENCES entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: link_range_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY link
    ADD CONSTRAINT link_range_id_fkey FOREIGN KEY (range_id) REFERENCES entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: property_domain_class_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property
    ADD CONSTRAINT property_domain_class_id_fkey FOREIGN KEY (domain_class_id) REFERENCES class(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: property_inheritance_sub_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property_inheritance
    ADD CONSTRAINT property_inheritance_sub_id_fkey FOREIGN KEY (sub_id) REFERENCES property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: property_inheritance_super_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property_inheritance
    ADD CONSTRAINT property_inheritance_super_id_fkey FOREIGN KEY (super_id) REFERENCES property(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: property_range_class_id_fkey; Type: FK CONSTRAINT; Schema: model; Owner: openatlas_master
--

ALTER TABLE ONLY property
    ADD CONSTRAINT property_range_class_id_fkey FOREIGN KEY (range_class_id) REFERENCES class(id) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = web, pg_catalog;

--
-- Name: hierarchy_form_form_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy_form
    ADD CONSTRAINT hierarchy_form_form_id_fkey FOREIGN KEY (form_id) REFERENCES form(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hierarchy_form_hierarchy_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy_form
    ADD CONSTRAINT hierarchy_form_hierarchy_id_fkey FOREIGN KEY (hierarchy_id) REFERENCES hierarchy(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hierarchy_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY hierarchy
    ADD CONSTRAINT hierarchy_id_fkey FOREIGN KEY (id) REFERENCES model.entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: i18n_item_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY i18n
    ADD CONSTRAINT i18n_item_id_fkey FOREIGN KEY (item_id) REFERENCES content(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: i18n_language_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY i18n
    ADD CONSTRAINT i18n_language_id_fkey FOREIGN KEY (language_id) REFERENCES language(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_bookmarks_entity_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_bookmarks
    ADD CONSTRAINT user_bookmarks_entity_id_fkey FOREIGN KEY (entity_id) REFERENCES model.entity(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_bookmarks_user_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_bookmarks
    ADD CONSTRAINT user_bookmarks_user_id_fkey FOREIGN KEY (user_id) REFERENCES "user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_group_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_group_id_fkey FOREIGN KEY (group_id) REFERENCES "group"(id) ON UPDATE CASCADE;


--
-- Name: user_settings_user_id_fkey; Type: FK CONSTRAINT; Schema: web; Owner: openatlas_master
--

ALTER TABLE ONLY user_settings
    ADD CONSTRAINT user_settings_user_id_fkey FOREIGN KEY (user_id) REFERENCES "user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

