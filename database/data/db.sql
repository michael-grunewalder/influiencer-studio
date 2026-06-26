--
-- PostgreSQL database dump
--

\restrict Ma3sTpoau85B9dTgU0vcW5gyJSoywFthLGx8pYpoSr7OyRHdT3tCPWlNVlAUnlQ

-- Dumped from database version 18.2 (Debian 18.2-1.pgdg13+1)
-- Dumped by pg_dump version 18.3 (Debian 18.3-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO db;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO db;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO db;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: db
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO db;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: influencers; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.influencers (
    id character(26) NOT NULL,
    name character varying(255),
    stagename character varying(255),
    avatar character varying(255),
    bio text,
    properties jsonb,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    team_id character(26) NOT NULL
);


ALTER TABLE public.influencers OWNER TO db;

--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO db;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO db;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: db
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO db;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO db;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: db
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO db;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.model_has_permissions (
    permission_id character(26) NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id character(26) NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO db;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.model_has_roles (
    role_id character(26) NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id character(26) NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO db;

--
-- Name: otp_codes; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.otp_codes (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    code character varying(6) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.otp_codes OWNER TO db;

--
-- Name: otp_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: db
--

CREATE SEQUENCE public.otp_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.otp_codes_id_seq OWNER TO db;

--
-- Name: otp_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db
--

ALTER SEQUENCE public.otp_codes_id_seq OWNED BY public.otp_codes.id;


--
-- Name: outfits; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.outfits (
    id character(26) NOT NULL,
    influencer_id character(26) NOT NULL,
    name character varying(255),
    top character varying(255),
    bottom character varying(255),
    hairstyle character varying(255),
    full_look_description text,
    image_path character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.outfits OWNER TO db;

--
-- Name: passkeys; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.passkeys (
    id bigint NOT NULL,
    authenticatable_id character(26) NOT NULL,
    name text NOT NULL,
    credential_id text NOT NULL,
    data json NOT NULL,
    last_used_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.passkeys OWNER TO db;

--
-- Name: passkeys_id_seq; Type: SEQUENCE; Schema: public; Owner: db
--

CREATE SEQUENCE public.passkeys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.passkeys_id_seq OWNER TO db;

--
-- Name: passkeys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: db
--

ALTER SEQUENCE public.passkeys_id_seq OWNED BY public.passkeys.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO db;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.permissions (
    id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO db;

--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.role_has_permissions (
    permission_id character(26) NOT NULL,
    role_id character(26) NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO db;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.roles (
    id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO db;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id character(26),
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO db;

--
-- Name: team_assets; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.team_assets (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    local_url character varying(255) NOT NULL,
    remote_url text,
    mime_type character varying(255),
    purpose character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    meta_data json
);


ALTER TABLE public.team_assets OWNER TO db;

--
-- Name: team_invitations; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.team_invitations (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    email character varying(255) NOT NULL,
    name character varying(255),
    token character varying(255) NOT NULL,
    invited_by character(26),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.team_invitations OWNER TO db;

--
-- Name: team_user; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.team_user (
    team_id character(26) NOT NULL,
    user_id character(26) NOT NULL,
    role character varying(255) DEFAULT 'view'::character varying NOT NULL
);


ALTER TABLE public.team_user OWNER TO db;

--
-- Name: teams; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.teams (
    id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    fal_api_key character varying(255),
    credits numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    claude_api_key character varying(255)
);


ALTER TABLE public.teams OWNER TO db;

--
-- Name: transactions; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.transactions (
    id character(26) NOT NULL,
    user_id character(26),
    team_id character(26),
    type character varying(255) NOT NULL,
    amount numeric(8,2) NOT NULL,
    description character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.transactions OWNER TO db;

--
-- Name: users; Type: TABLE; Schema: public; Owner: db
--

CREATE TABLE public.users (
    id character(26) NOT NULL,
    first_name character varying(255) NOT NULL,
    middle_name character varying(255),
    last_name character varying(255),
    email character varying(255) NOT NULL,
    avatar character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    credits numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    default_team_id character(26),
    team_selection_mode character varying(255) DEFAULT 'default'::character varying NOT NULL,
    last_active_team_id character(26)
);


ALTER TABLE public.users OWNER TO db;

--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: otp_codes id; Type: DEFAULT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.otp_codes ALTER COLUMN id SET DEFAULT nextval('public.otp_codes_id_seq'::regclass);


--
-- Name: passkeys id; Type: DEFAULT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.passkeys ALTER COLUMN id SET DEFAULT nextval('public.passkeys_id_seq'::regclass);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: influencers influencers_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.influencers
    ADD CONSTRAINT influencers_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: otp_codes otp_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.otp_codes
    ADD CONSTRAINT otp_codes_pkey PRIMARY KEY (id);


--
-- Name: outfits outfits_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.outfits
    ADD CONSTRAINT outfits_pkey PRIMARY KEY (id);


--
-- Name: passkeys passkeys_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.passkeys
    ADD CONSTRAINT passkeys_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: team_assets team_assets_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_assets
    ADD CONSTRAINT team_assets_pkey PRIMARY KEY (id);


--
-- Name: team_invitations team_invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_invitations
    ADD CONSTRAINT team_invitations_pkey PRIMARY KEY (id);


--
-- Name: team_invitations team_invitations_token_unique; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_invitations
    ADD CONSTRAINT team_invitations_token_unique UNIQUE (token);


--
-- Name: team_user team_user_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_user
    ADD CONSTRAINT team_user_pkey PRIMARY KEY (team_id, user_id);


--
-- Name: teams teams_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.teams
    ADD CONSTRAINT teams_pkey PRIMARY KEY (id);


--
-- Name: transactions transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: otp_codes_email_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX otp_codes_email_index ON public.otp_codes USING btree (email);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: team_assets_local_url_index; Type: INDEX; Schema: public; Owner: db
--

CREATE INDEX team_assets_local_url_index ON public.team_assets USING btree (local_url);


--
-- Name: influencers influencers_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.influencers
    ADD CONSTRAINT influencers_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: outfits outfits_influencer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.outfits
    ADD CONSTRAINT outfits_influencer_id_foreign FOREIGN KEY (influencer_id) REFERENCES public.influencers(id) ON DELETE CASCADE;


--
-- Name: passkeys passkeys_authenticatable_fk; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.passkeys
    ADD CONSTRAINT passkeys_authenticatable_fk FOREIGN KEY (authenticatable_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: team_assets team_assets_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_assets
    ADD CONSTRAINT team_assets_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE CASCADE;


--
-- Name: team_invitations team_invitations_invited_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_invitations
    ADD CONSTRAINT team_invitations_invited_by_foreign FOREIGN KEY (invited_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: team_invitations team_invitations_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_invitations
    ADD CONSTRAINT team_invitations_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE CASCADE;


--
-- Name: team_user team_user_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_user
    ADD CONSTRAINT team_user_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE CASCADE;


--
-- Name: team_user team_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.team_user
    ADD CONSTRAINT team_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.teams(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: users users_default_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_default_team_id_foreign FOREIGN KEY (default_team_id) REFERENCES public.teams(id) ON DELETE SET NULL;


--
-- Name: users users_last_active_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: db
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_last_active_team_id_foreign FOREIGN KEY (last_active_team_id) REFERENCES public.teams(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict Ma3sTpoau85B9dTgU0vcW5gyJSoywFthLGx8pYpoSr7OyRHdT3tCPWlNVlAUnlQ

