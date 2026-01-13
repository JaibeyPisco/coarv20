n 

create table asistencia
(
    id            int auto_increment
        primary key,
    fecha         date                                not null,
    fecha_sistema timestamp default CURRENT_TIMESTAMP null,
    turno         varchar(20)                         not null,
    id_usuario    int                                 not null,
    grado_seccion varchar(10)                         not null,
    updated_at    timestamp                           null,
    id_empresa    int                                 null,
    constraint idx_unique_asistencia_sesion
        unique (fecha, grado_seccion, turno)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create index asistencia_usuario__fk
    on asistencia (id_usuario);

create table asistencia_detalle
(
    id            int auto_increment
        primary key,
    fecha_sistema timestamp default CURRENT_TIMESTAMP null,
    id_asistencia int                                 not null,
    id_estudiante int                                 not null,
    estado        varchar(30)                         not null,
    observacion   text                                null,
    updated_at    timestamp                           null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create index asistencia_detalle_estudiante_id_fk
    on asistencia_detalle (id_estudiante);

create table centinela
(
    id          bigint auto_increment
        primary key,
    fecha       datetime default CURRENT_TIMESTAMP null,
    id_usuario  int                                null,
    modulo      varchar(100)                       null,
    descripcion varchar(1000)                      null,
    id_empresa  int                                null,
    menu        varchar(100)                       null,
    accion      varchar(45)                        null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table ci_sessions
(
    id         varchar(128)                        not null
        primary key,
    ip_address varchar(45)                         null,
    timestamp  timestamp default CURRENT_TIMESTAMP not null,
    data       blob                                not null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create index ci_sessions_timestamp
    on ci_sessions (timestamp);

create table comentario_incidencia
(
    id            int auto_increment
        primary key,
    accion        char(50) null,
    id_area       int      null,
    id_incidencia int      null,
    id_usuario    int      null,
    comentario    text     null,
    fecha         date     null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table empresa
(
    id                         int           not null
        primary key,
    razon_social               varchar(500)  null,
    nombre_comercial           varchar(500)  null,
    numero_documento           varchar(11)   null,
    direccion                  varchar(300)  null,
    telefono                   varchar(20)   null,
    email                      varchar(100)  null,
    logo                       varchar(500)  null,
    id_membresia               int           null,
    origen_factura             varchar(45)   null,
    formato_factura            varchar(45)   null,
    
    id_ubigeo                  varchar(6)    null,
   
    constraint id_membresia
        unique (id_membresia)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table ajuste_avanzado
(
    id_empresa                        int         not null
        primary key,
    porcentaje_detraccion             double      null,
    porcentaje_igv                    double      null,
    fl_sistema_logo                   tinyint     null,
    fl_sistema_change_color           tinyint     null,
    sistema_color_bg                  varchar(45) null,
    fl_pagar_factura                  tinyint     null,
    fl_tarifa_articulo                tinyint     null,
    fl_tarifa_cliente                 tinyint     null,
    fl_tarifa_ruta                    tinyint     null,
    fl_op_os_doc_ref                  tinyint     null,
    fl_op_clave_edit_orden            tinyint     null,
    fl_op_multiple_orden_gt           tinyint     null,
    fl_op_emision_electronico_grt     tinyint     null,
    fl_general_local_independiente    tinyint     null,
    fl_op_emision_electronico_grr     tinyint     null,
    fl_op_os_emision_grt              tinyint     null,
    fl_facturacion_envio_manual_sunat tinyint     null,
    cant_decimales_venta              varchar(10) null,
    fl_fact_detraccion_automatica     tinyint     null,
    fl_op_os_descripcion_articulo     tinyint     null,
    fl_op_tarifa_info                 tinyint     null,
    fl_general_serie_usuario          tinyint     null,
    porcentaje_izipay                 double      null,
    termino_condicion_orden           text        null,
    constraint ajuste_avanzado_empresa_id_fk
        foreign key (id_empresa) references empresa (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table estado_monitoreo
(
    id         int auto_increment
        primary key,
    nombre     varchar(100) null,
    id_empresa int          null,
    tipo       varchar(45)  null,
    color_bg   varchar(45)  null,
    color_text varchar(45)  null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table estudiante
(
    id                          int auto_increment
        primary key,
    nombres                     varchar(255) null,
    apellidos                   varchar(100) null,
    obsv                        varchar(255) null,
    grado                       varchar(10)  null,
    seccion                     varchar(10)  null,
    dni                         varchar(20)  null,
    foto                        varchar(255) null,
    sexo                        varchar(20)  null,
    correo_electronico          varchar(255) null,
    codigo_estudiante           varchar(255) null,
    fecha_nacimiento            date         null,
    lav                         varchar(10)  null,
    llaves                      varchar(10)  null,
    pabellon                    varchar(10)  null,
    ala                         varchar(10)  null,
    cama_ropero                 varchar(10)  null,
    duchas                      varchar(10)  null,
    banos                       varchar(10)  null,
    urinarios                   varchar(10)  null,
    monitor_acompana            varchar(255) null,
    lugar_nacimiento            varchar(255) null,
    fecha_caducidad_dni         date         null,
    num_telefonico              varchar(20)  null,
    religion                    varchar(50)  null,
    region_domicilio_actual     varchar(50)  null,
    provincia_domicilio_actual  varchar(50)  null,
    distrito_domicilio_actual   varchar(50)  null,
    direccion_domicilio_actual  varchar(255) null,
    referencia_domicilio_actual varchar(255) null,
    condicion_estudiante        varchar(20)  null,
    constraint dni_pks
        unique (dni)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table incidencia_reporte_detalle
(
    id                    int not null
        primary key,
    id_incidencia_reporte int null,
    id_incidencia         int null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table lugar
(
    id         int auto_increment
        primary key,
    nombre     varchar(100) null,
    id_empresa int          null,
    referencia varchar(100) null,
    estado     tinyint      null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table membresia
(
    id                int auto_increment
        primary key,
    nombre            varchar(300) null,
    token_integracion varchar(500) null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table orden_columnas_datatable
(
    id_usuario        int                          null,
    id_empresa        int                          null,
    modulo            varchar(45)                  null,
    columnas_visibles longtext collate utf8mb4_bin null,
    orden_columnas    longtext collate utf8mb4_bin null,
    id                int auto_increment
        primary key,
    check (json_valid(`columnas_visibles`)),
    check (json_valid(`orden_columnas`))
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create index id_empresa_idx
    on orden_columnas_datatable (id_empresa, id_usuario);

create index id_usuario_pk_idx
    on orden_columnas_datatable (id_usuario);

create table padres_apoderados
(
    id                            int auto_increment
        primary key,
    vive                          char         null,
    vive_con_estudiante           char         null,
    nombres                       varchar(255) null,
    apellidos                     varchar(100) null,
    grado_instruccion             varchar(50)  null,
    ocupacion_actual              varchar(255) null,
    correo_electronico            varchar(255) null,
    telefono                      varchar(50)  null,
    motivo_no_vive_con_estudiante varchar(255) null,
    tipo                          varchar(30)  null,
    fl_legalizado                 int          null,
    foto                          varchar(100) null,
    parentesco_estudiante         varchar(20)  null,
    id_estudiante                 int          null,
    dni                           int          null,
    tipo_familia                  varchar(40)  null,
    constraint padres_apoderados_estudiante_id_fk
        foreign key (id_estudiante) references estudiante (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table permiso
(
    id       bigint       not null,
    view     tinyint      null,
    new      tinyint      null,
    edit     tinyint      null,
    `delete` tinyint      null,
    id_rol   int          null,
    menu     varchar(100) null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table proveedor
(
    id               int auto_increment
        primary key,
    id_documento     int          null,
    numero_documento varchar(255) null,
    razon_social     varchar(255) null,
    correo           varchar(255) null,
    direccion        text         null,
    telefono         varchar(20)  null,
    id_area          int          null,
    id_usuario       int          null,
    estado           varchar(20)  null,
    contacto_nombre  varchar(50)  null,
    imagen           varchar(200) null,
    id_ubigeo        int          null,
    id_empresa       int          null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table rol
(
    id              int auto_increment
        primary key,
    nombre          varchar(50) null,
    id_empresa      int         null,
    fl_no_dashboard tinyint     null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table static_documento
(
    id           int auto_increment
        primary key,
    nombre       varchar(100) null,
    codigo_sunat varchar(100) null,
    tipo         varchar(100) null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table static_grado
(
    id          int auto_increment
        primary key,
    nombre      varchar(100) null,
    descripcion varchar(100) null,
    estado      varchar(45)  null,
    nivel       varchar(45)  null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table static_seccion
(
    id          int auto_increment
        primary key,
    nombre      varchar(100) null,
    grado_id    int          null,
    estado      varchar(45)  null,
    descripcion varchar(100) null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table static_system
(
    id                 int          not null,
    email_robot        varchar(100) null,
    empresa            varchar(300) null,
    bg_login           varchar(500) null,
    fl_bg_login        tinyint      null,
    fl_logo_login      tinyint      null,
    logo_login         varchar(500) null,
    color_button_login varchar(45)  null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table static_ubigeo
(
    id           varchar(6)   not null
        primary key,
    departamento varchar(120) null,
    provincia    varchar(120) null,
    distrito     varchar(120) null
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table tipo_personal
(
    id          int auto_increment
        primary key,
    nombre      varchar(255) null,
    descripcion varchar(555) null,
    id_usuario  int          not null,
    estado      int          not null,
    id_empresa  int          null,
    constraint tipo_personal_pk
        unique (nombre)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table personal
(
    id                int auto_increment
        primary key,
    numero_documento  int default 0 not null,
    id_tipo_personal  int           null,
    imagen            varchar(50)   null,
    firma             varchar(50)   null,
    codigo            varchar(255)  null,
    nombre            varchar(200)  null,
    apellido          varchar(200)  null,
    telefono          varchar(255)  null,
    estado            int           not null,
    id_tipo_documento int           null,
    tipo_contratacion varchar(50)   null,
    direccion         varchar(100)  null,
    ubigeo            varchar(100)  null,
    comentario        varchar(100)  null,
    `Columna 16`      int           null,
    id_proveedor      int           null,
    constraint personal_proveedor_id_fk
        foreign key (id_proveedor) references proveedor (id),
    constraint personal_tipo_personal_id_fk
        foreign key (id_tipo_personal) references tipo_personal (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table tipos_incidencias
(
    id                   int auto_increment
        primary key,
    nombre               varchar(50)       null,
    estado               tinyint default 1 not null,
    nivel_incidencia     varchar(50)       null,
    id_usuario           int               null,
    id_empresa           int               null,
    nivel_severidad      varchar(100)      null,
    color_bg             varchar(100)      null,
    color_text           varchar(100)      null,
    derivacion_inmediata enum ('SI', 'NO') null,
    constraint nombre
        unique (nombre),
    constraint tipos_incidencias_empresa_id_fk
        foreign key (id_empresa) references empresa (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table usuario
(
    id                         int auto_increment
        primary key,
    imagen                     varchar(500)  null,
    nombre                     varchar(45)   null,
    apellido                   varchar(45)   null,
    usuario                    varchar(100)  null,
    email                      varchar(100)  null,
    password                   char(250)     null,
    salt                       char(250)     null,
    color                      varchar(255)  null,
    id_membresia               int           null,
    tipo                       varchar(45)   null,
    id_personal                int           null,
    id_rol                     int           null,
    login_date                 varchar(45)   null,
    token                      varchar(1000) null,
    id_empresa                 int           null,
    id_local                   int           null,
    id_area                    int           null,
    fl_suspendido              tinyint       null,
    tipo_persona               varchar(45)   null,
    id_cliente                 int           null,
    fl_cambio_local            tinyint       null,
    fl_ver_derivaciones        tinyint       null,
    estado                     int           not null,
    id_estudiante              int           null,
    fl_ver_informacion_privada int           null,
    email_verified_at          varchar(100)  null,
    remember_token             varchar(100)  null,
    created_at                 timestamp     null,
    updated_at                 timestamp     null,
    constraint usuario_pk_unicos
        unique (usuario, email),
    constraint id_empresa_usuario_pk
        foreign key (id_empresa) references empresa (id),
    constraint id_personal_usuario_pk
        foreign key (id_personal) references personal (id),
    constraint id_rol_usuario_pk
        foreign key (id_rol) references rol (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table incidencias
(
    id                     bigint unsigned auto_increment
        primary key,
    serie                  text                                    null,
    numero                 text                                    null,
    id_tipo_incidencia     int       default 0                     null,
    id_estudiante          int       default 0                     null,
    descripcion            text                                    null,
    id_usuario             int       default 0                     null,
    fecha                  datetime                                not null,
    created_at             timestamp default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    updated_at             timestamp default '0000-00-00 00:00:00' not null,
    id_lugar_incidencia    int                                     null,
    fl_estado              int                                     null,
    motivo_anulacion       varchar(100)                            null,
    estado                 varchar(20)                             null,
    motivo_derivacion      varchar(100)                            null,
    motivo_finalizacion    varchar(200)                            null,
    id_personal_derivado   int                                     null,
    id_area                int                                     null,
    id_usuario_derivador   int                                     null,
    id_usuario_finalizador int                                     null,
    constraint id_area_incidencia_fk
        foreign key (id_area) references area (id),
    constraint id_personal_derivado_fk
        foreign key (id_personal_derivado) references personal (id),
    constraint id_usuario_derivador
        foreign key (id_usuario_derivador) references usuario (id),
    constraint id_usuario_finalizado_incidencia_fk
        foreign key (id_usuario_finalizador) references usuario (id),
    constraint id_usuario_incidencia_fk
        foreign key (id_usuario) references usuario (id),
    constraint incidencia_lugar_id_fk
        foreign key (id_lugar_incidencia) references lugar (id),
    constraint incidencias_estudiante_id_fk
        foreign key (id_estudiante) references estudiante (id),
    constraint incidencias_tipos_incidencias_id_fk
        foreign key (id_tipo_incidencia) references tipos_incidencias (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table incidencia_monitoreo
(
    id                  int auto_increment
        primary key,
    fecha_hora          datetime                                not null,
    id_estado_monitoreo int                                     not null,
    problema            longtext                                null,
    acuerdos            longtext                                null,
    id_incidencia       bigint unsigned                         not null,
    descripcion_privada longtext                                null,
    id_usuario          int                                     null,
    id_empresa          int                                     null,
    archivo             varchar(100)                            null,
    created_at          timestamp default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    updated_at          timestamp default '0000-00-00 00:00:00' not null,
    constraint incidencia_monitoreo_estado_monitoreo_id_fk
        foreign key (id_estado_monitoreo) references estado_monitoreo (id),
    constraint incidencia_monitoreo_incidencias_id_fk
        foreign key (id_incidencia) references incidencias (id),
    constraint incidencia_monitoreo_usuario_id_fk
        foreign key (id_usuario) references usuario (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table incidencia_archivo
(
    id                      int auto_increment
        primary key,
    id_incidencia           bigint unsigned null,
    archivo                 varchar(200)    not null,
    nombre                  varchar(50)     null,
    id_incidencia_monitoreo int             null,
    constraint incidencia_archivo_incidencia_monitoreo_id_fk
        foreign key (id_incidencia_monitoreo) references incidencia_monitoreo (id),
    constraint incidencia_archivos_incidencias_id_fk
        foreign key (id_incidencia) references incidencias (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create table incidencia_evento
(
    id            int auto_increment
        primary key,
    id_usuario    varchar(45)                         null,
    evento        varchar(45)                         null,
    origen        varchar(45)                         null,
    id_incidencia varchar(45)                         null,
    id_empresa    varchar(45)                         null,
    fecha         date                                null,
    fecha_sistema timestamp default CURRENT_TIMESTAMP null,
    descripcion   varchar(250)                        null,
    id_area       int                                 null,
    id_monitoreo  int                                 null,
    constraint id_area_pk
        foreign key (id_area) references area (id),
    constraint id_monitoreo_evento_pk
        foreign key (id_monitoreo) references incidencia_monitoreo (id)
)
    engine = InnoDB
    collate = utf8mb4_general_ci;

create index id_area_pk_idx
    on incidencia_evento (id_area);

create index id_monitoreo_evento_pk_idx
    on incidencia_evento (id_monitoreo);

create index incidencia_monitoreo_empresa_id_fk
    on incidencia_monitoreo (id_empresa);

create index id_area_incidencia_fk_idx
    on incidencias (id_area);

create index id_usuario_derivador_idx
    on incidencias (id_usuario_derivador);

create index id_usuario_finalizado_incidencia_fk_idx
    on incidencias (id_usuario_finalizador);

create index id_usuario_incidencia_fk_idx
    on incidencias (id_usuario);

create index id_empresa_usuario_pk_idx
    on usuario (id_empresa);

create index id_personal_usuario_pk_idx
    on usuario (id_personal);

create index id_rol_usuario_pk_idx
    on usuario (id_rol);

