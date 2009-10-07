--
-- Name: users; Type: TABLE; Schema: public; Owner: pg_default_user; Tablespace:
--

CREATE TABLE users (
    id integer NOT NULL,
    group_id integer DEFAULT 2 NOT NULL,
    username character varying(20) NOT NULL,
    password character varying(40) NOT NULL,
    salt character varying(20) NOT NULL,
    hash character varying(40) NOT NULL,
    email character varying(255) NOT NULL
);

--
-- Name: users_group_id_seq; Type: SEQUENCE; Schema: public; Owner: pg_default_user
--

CREATE SEQUENCE users_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Name: users_group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pg_default_user
--

ALTER SEQUENCE users_group_id_seq OWNED BY users.group_id;


--
-- Name: users_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pg_default_user
--

SELECT pg_catalog.setval('users_group_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: pg_default_user
--

CREATE SEQUENCE users_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pg_default_user
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;

--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pg_default_user
--

SELECT pg_catalog.setval('users_id_seq', 1, true);

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: pg_default_user
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);

--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pg_default_user
--

COPY users (id, group_id, username, password, salt, hash, email) FROM stdin;
1	1	Frank	d65c4e4ccf8118baf64877e8ad0b8496805525b1	/z{!j2Xn<q=:I"Tnd;,_	ad311415064cf8742acf52f4c731c4f3dccc4769	example@example.com
\.

--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: pg_default_user; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);

--
-- Name: users_username_key; Type: CONSTRAINT; Schema: public; Owner: pg_default_user; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_username_key UNIQUE (username);
