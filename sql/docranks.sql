-- *********************************************
-- * SQL MySQL generation                      
-- *--------------------------------------------
-- * DB-MAIN version: 11.0.2              
-- * Generator date: Sep 14 2021              
-- * Generation date: Fri Jul 18 15:13:58 2025 
-- * LUN file: C:\Users\andre\Pictures\TESIDILAUREA.lun 
-- * Schema: docranks/7 
-- ********************************************* 


-- Database Section
-- ________________ 

create database docranks;
use docranks;


-- Tables Section
-- _____________ 

create table AREE (
     nome_area varchar(50) not null,
     constraint ID_AREE_ID primary key (nome_area));

create table ARTICOLI (
     titolo varchar(200) not null,
     anno int not null,
     numero_autori int not null,
     DOI varchar(100) not null,
     nome_autori varchar(150) not null,
     EFWCI float(1),
     FWCI float(1),
     citation_count int,
     scopus_id char(50),
     dblpRivista varchar(100) not null,
     id varchar(50),
     constraint ID_ARTICOLI_ID primary key (DOI));

create table ATTI_DI_CONVEGNO (
     titolo varchar(200) not null,
     anno int not null,
     numero_autori int not null,
     DOI varchar(100) not null,
     nome_autori varchar(150) not null,
     EFWCI float(1),
     FWCI float(1),
     citation_count int,
     scopus_id char(50),
     acronimo varchar(20),
     constraint ID_ATTI_DI_CONVEGNO_ID primary key (DOI));

create table AUTORI (
     nome varchar(20) not null,
     cognome varchar(20) not null,
     scopus_id char(20) not null,
     h_index float(1),
     numero_riviste int not null,
     numero_citazioni int not null,
     numero_documenti int not null,
     constraint ID_AUTORI_ID primary key (scopus_id));

create table CATEGORIE (
     nome_categoria varchar(50) not null,
     nome_area varchar(50),
     constraint ID_CATEGORIE_ID primary key (nome_categoria));

create table CONFERENZE (
     titolo varchar(150) not null,
     acronimo varchar(20) not null,
     constraint ID_CONFERENZE_ID primary key (acronimo));

create table INFO_CONF (
     acronimo varchar(20) not null,
     anno int not null,
     valore char(2),
     constraint ID_INFO_CONF_ID primary key (acronimo, anno));

create table INFORMAZIONI_AUTORI (
     scopus_id char(20) not null,
     anno int not null,
     documenti int not null,
     citazioni int not null,
     constraint ID_INFORMAZIONI_AUTORI_ID primary key (scopus_id, anno));

create table INFORMAZIONI_RIVISTE (
     id varchar(50) not null,
     anno int not null,
     SJR float(1) not null,
     SNIP float(1),
     miglior_quartile char(2) not null,
     classifica int not null,
     CiteScore float(1),
     constraint ID_INFORMAZIONI_RIVISTE_ID primary key (id, anno));

create table PARTECIPAZIONE (
     DOI varchar(100) not null,
     scopus_id char(20) not null,
     constraint ID_PARTECIPAZIONE_ID primary key (DOI, scopus_id));

create table QUARTILI (
     nome_categoria varchar(50) not null,
     id varchar(50) not null,
     valore int not null,
     anno int not null,
     constraint ID_QUARTILI_ID primary key (id, nome_categoria, anno));

create table RANKING_1 (
     valore char(2) not null,
     constraint ID_RANKING_1_ID primary key (valore));

create table REDAZIONE (
     DOI varchar(100) not null,
     scopus_id char(20) not null,
     constraint ID_REDAZIONE_ID primary key (DOI, scopus_id));

create table RIVISTE (
     id varchar(50) not null,
     nome varchar(100) not null,
     link_rivista varchar(100) not null,
     publisher varchar(20) not null,
     issn varchar(40) not null,
     constraint ID_RIVISTE_ID primary key (id));

create table SPECIALIZZAZIONI_AREA (
     nome_area varchar(50) not null,
     id varchar(50) not null,
     constraint ID_SPECIALIZZAZIONI_AREA_ID primary key (nome_area, id));

create table SPECIALIZZAZIONI_CATEGORIE (
     nome_categoria varchar(50) not null,
     id varchar(50) not null,
     constraint ID_SPECIALIZZAZIONI_CATEGORIE_ID primary key (nome_categoria, id));


-- Constraints Section
-- ___________________ 

alter table ARTICOLI add constraint FKAPPARTENENZA_A_R_FK
     foreign key (id)
     references RIVISTE (id);

alter table ATTI_DI_CONVEGNO add constraint FKAPPARTENENZA_A_C_FK
     foreign key (acronimo)
     references CONFERENZE (acronimo);

-- Not implemented
-- alter table AUTORI add constraint ID_AUTORI_CHK
--     check(exists(select * from INFORMAZIONI_AUTORI
--                  where INFORMAZIONI_AUTORI.scopus_id = scopus_id)); 

alter table CATEGORIE add constraint FKSOTTOCASTEGORIA_FK
     foreign key (nome_area)
     references AREE (nome_area);

-- Not implemented
-- alter table CONFERENZE add constraint ID_CONFERENZE_CHK
--     check(exists(select * from ATTI_DI_CONVEGNO
--                  where ATTI_DI_CONVEGNO.acronimo = acronimo)); 

alter table INFO_CONF add constraint FKRANK_CONF_1_FK
     foreign key (valore)
     references RANKING_1 (valore);

alter table INFO_CONF add constraint FKINFO_CONF_A
     foreign key (acronimo)
     references CONFERENZE (acronimo);

alter table INFORMAZIONI_AUTORI add constraint FKINFO_ANNO_A
     foreign key (scopus_id)
     references AUTORI (scopus_id);

alter table INFORMAZIONI_RIVISTE add constraint FKINFO_ANNO_R
     foreign key (id)
     references RIVISTE (id);

alter table PARTECIPAZIONE add constraint FKPAR_AUT_FK
     foreign key (scopus_id)
     references AUTORI (scopus_id);

alter table PARTECIPAZIONE add constraint FKPAR_ATT
     foreign key (DOI)
     references ATTI_DI_CONVEGNO (DOI);

alter table QUARTILI add constraint FKOTTIENE
     foreign key (id)
     references RIVISTE (id);

alter table QUARTILI add constraint FKCORRISPONDENZA_FK
     foreign key (nome_categoria)
     references CATEGORIE (nome_categoria);

alter table REDAZIONE add constraint FKRED_AUT_FK
     foreign key (scopus_id)
     references AUTORI (scopus_id);

alter table REDAZIONE add constraint FKRED_ART
     foreign key (DOI)
     references ARTICOLI (DOI);

-- Not implemented
-- alter table RIVISTE add constraint ID_RIVISTE_CHK
--     check(exists(select * from INFORMAZIONI_RIVISTE
--                  where INFORMAZIONI_RIVISTE.id = id)); 

alter table SPECIALIZZAZIONI_AREA add constraint FKSPE_RIV_1_FK
     foreign key (id)
     references RIVISTE (id);

alter table SPECIALIZZAZIONI_AREA add constraint FKSPE_ARE
     foreign key (nome_area)
     references AREE (nome_area);

alter table SPECIALIZZAZIONI_CATEGORIE add constraint FKSPE_RIV_FK
     foreign key (id)
     references RIVISTE (id);

alter table SPECIALIZZAZIONI_CATEGORIE add constraint FKSPE_CAT
     foreign key (nome_categoria)
     references CATEGORIE (nome_categoria);


-- Index Section
-- _____________ 

create unique index ID_AREE_IND
     on AREE (nome_area);

create unique index ID_ARTICOLI_IND
     on ARTICOLI (DOI);

create index FKAPPARTENENZA_A_R_IND
     on ARTICOLI (id);

create unique index ID_ATTI_DI_CONVEGNO_IND
     on ATTI_DI_CONVEGNO (DOI);

create index FKAPPARTENENZA_A_C_IND
     on ATTI_DI_CONVEGNO (acronimo);

create unique index ID_AUTORI_IND
     on AUTORI (scopus_id);

create unique index ID_CATEGORIE_IND
     on CATEGORIE (nome_categoria);

create index FKSOTTOCASTEGORIA_IND
     on CATEGORIE (nome_area);

create unique index ID_CONFERENZE_IND
     on CONFERENZE (acronimo);

create unique index ID_INFO_CONF_IND
     on INFO_CONF (acronimo, anno);

create index FKRANK_CONF_1_IND
     on INFO_CONF (valore);

create unique index ID_INFORMAZIONI_AUTORI_IND
     on INFORMAZIONI_AUTORI (scopus_id, anno);

create unique index ID_INFORMAZIONI_RIVISTE_IND
     on INFORMAZIONI_RIVISTE (id, anno);

create unique index ID_PARTECIPAZIONE_IND
     on PARTECIPAZIONE (DOI, scopus_id);

create index FKPAR_AUT_IND
     on PARTECIPAZIONE (scopus_id);

create unique index ID_QUARTILI_IND
     on QUARTILI (id, nome_categoria, anno);

create index FKCORRISPONDENZA_IND
     on QUARTILI (nome_categoria);

create unique index ID_RANKING_1_IND
     on RANKING_1 (valore);

create unique index ID_REDAZIONE_IND
     on REDAZIONE (DOI, scopus_id);

create index FKRED_AUT_IND
     on REDAZIONE (scopus_id);

create unique index ID_RIVISTE_IND
     on RIVISTE (id);

create unique index ID_SPECIALIZZAZIONI_AREA_IND
     on SPECIALIZZAZIONI_AREA (nome_area, id);

create index FKSPE_RIV_1_IND
     on SPECIALIZZAZIONI_AREA (id);

create unique index ID_SPECIALIZZAZIONI_CATEGORIE_IND
     on SPECIALIZZAZIONI_CATEGORIE (nome_categoria, id);

create index FKSPE_RIV_IND
     on SPECIALIZZAZIONI_CATEGORIE (id);

