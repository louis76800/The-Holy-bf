/*==============================================================*/
/* Nom de SGBD :  MySQL 5.0                                     */
/* Date de cr�ation :  25/03/2021 12:06:46                      */
/*==============================================================*/
CREATE DATABASE if not exists HOLY_BF;

drop table if exists associate;

drop table if exists contain_article;

drop table if exists contain_message;

drop table if exists own_post;

drop table if exists own_tag;

drop table if exists post;

drop table if exists t_article;

drop table if exists t_categorie;

drop table if exists t_media;

drop table if exists t_message;

drop table if exists t_role;

drop table if exists t_tag;

drop table if exists t_topic;

drop table if exists t_user;

/*==============================================================*/
/* Table : associate                                            */
/*==============================================================*/
create table associate
(
   ART_ID               int not null,
   CAT_ID               int not null,
   primary key (ART_ID, CAT_ID)
);

/*==============================================================*/
/* Table : contain_article                                      */
/*==============================================================*/
create table contain_article
(
   ART_ID               int not null,
   MED_ID               int not null,
   primary key (ART_ID, MED_ID)
);

/*==============================================================*/
/* Table : contain_message                                      */
/*==============================================================*/
create table contain_message
(
   MED_ID               int not null,
   MESS_ID              int not null,
   primary key (MED_ID, MESS_ID)
);

/*==============================================================*/
/* Table : own_post                                             */
/*==============================================================*/
create table own_post
(
   TOPIC_ID             int not null,
   TAG_ID               int not null,
   primary key (TOPIC_ID, TAG_ID)
);

/*==============================================================*/
/* Table : own_tag                                              */
/*==============================================================*/
create table own_tag
(
   ART_ID               int not null,
   TAG_ID               int not null,
   primary key (ART_ID, TAG_ID)
);

/*==============================================================*/
/* Table : post                                                 */
/*==============================================================*/
create table post
(
   CAT_ID               int not null,
   TOPIC_ID             int not null,
   primary key (CAT_ID, TOPIC_ID)
);

/*==============================================================*/
/* Table : t_article                                            */
/*==============================================================*/
create table t_article
(
   ART_ID               int not null auto_increment,
   USER_ID              int not null,
   ARt_categorie        varchar(255),
   ART_TITLE            varchar(255),
   ART_DATEADD          datetime,
   ID                   int,
   ART_URL              varchar(255),
   primary key (ART_ID)
);

/*==============================================================*/
/* Table : t_categorie                                          */
/*==============================================================*/
create table t_categorie
(
   CAT_ID               int not null auto_increment,
   CAT_NAME             varchar(255),
   CAT_DESCRIPTION      varchar(255),
   primary key (CAT_ID)
);

/*==============================================================*/
/* Table : t_media                                              */
/*==============================================================*/
create table t_media
(
   MED_ID               int not null auto_increment,
   MED_NAME             varchar(255),
   MED_FORMAT           varchar(255),
   MED_PATH             varchar(255),
   MED_ALT              varchar(255),
   primary key (MED_ID)
);

/*==============================================================*/
/* Table : t_message                                            */
/*==============================================================*/
create table t_message
(
   MESS_ID              int not null auto_increment,
   TOPIC_ID             int not null,
   USER_ID              int not null,
   MESS_TOPIC_ID        varchar(25),
   MESS_BODY            varchar(255),
   MESS_DATE            date,
   MESS_USER_ID         varchar(50),
   primary key (MESS_ID)
);

/*==============================================================*/
/* Table : t_role                                               */
/*==============================================================*/
create table t_role
(
   ROLE_ID              int not null,
   ROLE_NAME            varchar(50),
   primary key (ROLE_ID)
);

/*==============================================================*/
/* Table : t_tag                                                */
/*==============================================================*/
create table t_tag
(
   TAG_ID               int not null auto_increment,
   TAG_DESCRIPTION      varchar(255),
   primary key (TAG_ID)
);

/*==============================================================*/
/* Table : t_topic                                              */
/*==============================================================*/
create table t_topic
(
   TOPIC_ID             int not null auto_increment,
   USER_ID              int not null,
   TOPIC_TITLE          varchar(255),
   TOPIC_DESCRIPTION    varchar(255),
   TOPIC_AUTHOR_ID      varchar(255),
   TOPIC_DATE           datetime,
   TOPIC_URL            varchar(255),
   primary key (TOPIC_ID)
);

/*==============================================================*/
/* Table : t_user                                               */
/*==============================================================*/
create table t_user
(
   USER_ID              int not null auto_increment,
   ROLE_ID              int not null,
   USER_MAIL            varchar(100),
   USER_NICKNAME        varchar(25),
   USER_PASSWORD        varchar(255),
   primary key (USER_ID)
);

alter table associate add constraint FK_associate foreign key (ART_ID)
      references t_article (ART_ID) on delete restrict on update restrict;

alter table associate add constraint FK_associate2 foreign key (CAT_ID)
      references t_categorie (CAT_ID) on delete restrict on update restrict;

alter table contain_article add constraint FK_contain_article foreign key (ART_ID)
      references t_article (ART_ID) on delete restrict on update restrict;

alter table contain_article add constraint FK_contain_article2 foreign key (MED_ID)
      references t_media (MED_ID) on delete restrict on update restrict;

alter table contain_message add constraint FK_contain_message foreign key (MED_ID)
      references t_media (MED_ID) on delete restrict on update restrict;

alter table contain_message add constraint FK_contain_message2 foreign key (MESS_ID)
      references t_message (MESS_ID) on delete restrict on update restrict;

alter table own_post add constraint FK_own_post foreign key (TOPIC_ID)
      references t_topic (TOPIC_ID) on delete restrict on update restrict;

alter table own_post add constraint FK_own_post2 foreign key (TAG_ID)
      references t_tag (TAG_ID) on delete restrict on update restrict;

alter table own_tag add constraint FK_own_tag foreign key (ART_ID)
      references t_article (ART_ID) on delete restrict on update restrict;

alter table own_tag add constraint FK_own_tag2 foreign key (TAG_ID)
      references t_tag (TAG_ID) on delete restrict on update restrict;

alter table post add constraint FK_post foreign key (CAT_ID)
      references t_categorie (CAT_ID) on delete restrict on update restrict;

alter table post add constraint FK_post2 foreign key (TOPIC_ID)
      references t_topic (TOPIC_ID) on delete restrict on update restrict;

alter table t_article add constraint FK_WRITE foreign key (USER_ID)
      references t_user (USER_ID) on delete restrict on update restrict;

alter table t_message add constraint FK_HOLD foreign key (TOPIC_ID)
      references t_topic (TOPIC_ID) on delete restrict on update restrict;

alter table t_message add constraint FK_HOLD_MESSAGE foreign key (USER_ID)
      references t_user (USER_ID) on delete restrict on update restrict;

alter table t_topic add constraint FK_HOLD_post foreign key (USER_ID)
      references t_user (USER_ID) on delete restrict on update restrict;

alter table t_user add constraint FK_OWN foreign key (ROLE_ID)
      references t_role (ROLE_ID) on delete restrict on update restrict;

