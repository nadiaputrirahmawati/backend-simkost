CREATE TYPE "user_role" AS ENUM (
  'admin',
  'owner',
  'user'
);

CREATE TYPE "gender_type" AS ENUM (
  'L',
  'P'
);

CREATE TYPE "work_type" AS ENUM (
  'bekerja',
  'mahasiswa',
  'lainnya'
);

CREATE TYPE "marital_status_type" AS ENUM (
  'menikah',
  'belum_menikah'
);

CREATE TYPE "verification_status" AS ENUM (
  'unverified',
  'pending',
  'verified',
  'rejected'
);

CREATE TYPE "kost_type" AS ENUM (
  'campur',
  'putri',
  'putra'
);

CREATE TYPE "room_status" AS ENUM (
  'available',
  'occupied',
  'maintenance'
);

CREATE TYPE "contract_status" AS ENUM (
  'pending_payment',
  'active',
  'in_renewal',
  'completed',
  'cancelled'
);

CREATE TYPE "contract_type_enum" AS ENUM (
  'initial',
  'renewal'
);

CREATE TYPE "verification_contract_status" AS ENUM (
  'pending',
  'completed',
  'rejected'
);

CREATE TYPE "payment_status" AS ENUM (
  'pending',
  'completed',
  'failed',
  'expired'
);

CREATE TYPE "complaint_status" AS ENUM (
  'sent_in',
  'in_process',
  'completed'
);

CREATE TYPE "withdrawal_status" AS ENUM (
  'pending',
  'approved',
  'rejected'
);

CREATE TABLE "users" (
  "id" uuid PRIMARY KEY,
  "name" varchar(255) NOT NULL,
  "email" varchar(255) UNIQUE NOT NULL,
  "password" varchar(255) NOT NULL,
  "role" user_role NOT NULL DEFAULT 'user',
  "phone_number" varchar(20),
  "no_ktp" varchar(20) UNIQUE,
  "npwp" varchar(25),
  "gender" gender_type,
  "work" work_type,
  "tgl_lahir" date,
  "address" text,
  "marital_status" marital_status_type,
  "bank_name" varchar(100),
  "bank_account_number" varchar(50),
  "bank_account_holder" varchar(255),
  "balance" decimal(15,2) DEFAULT 0,
  "profile_picture" varchar(255),
  "ktp_picture" varchar(255),
  "ktp_picture_person" varchar(255),
  "status_verification" verification_status DEFAULT 'unverified',
  "rejection_feedback" text,
  "email_verified_at" timestamp,
  "remember_token" varchar(100),
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "kosts" (
  "id" uuid PRIMARY KEY,
  "owner_id" uuid NOT NULL,
  "name" varchar(255) NOT NULL,
  "slug" varchar(255) UNIQUE NOT NULL,
  "type" kost_type NOT NULL,
  "address" text NOT NULL,
  "city" varchar(100) NOT NULL,
  "description" text,
  "public_facility" jsonb,
  "regulation" jsonb,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "rooms" (
  "id" uuid PRIMARY KEY,
  "kost_id" uuid NOT NULL,
  "room_number" varchar(50) NOT NULL,
  "room_size" varchar(20),
  "price" decimal(15,2) NOT NULL,
  "deposit_amount" decimal(15,2) DEFAULT 0,
  "status" room_status NOT NULL DEFAULT 'available',
  "room_facility" jsonb,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "galleries" (
  "id" uuid PRIMARY KEY,
  "kost_id" uuid,
  "room_id" uuid,
  "image_url" varchar(255) NOT NULL,
  "is_primary" boolean DEFAULT false,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "contracts" (
  "id" uuid PRIMARY KEY,
  "owner_id" uuid NOT NULL,
  "user_id" uuid NOT NULL,
  "room_id" uuid NOT NULL,
  "start_date" date NOT NULL,
  "end_date" date NOT NULL,
  "monthly_price" decimal(15,2) NOT NULL,
  "deposit_amount" decimal(15,2) DEFAULT 0,
  "status" contract_status NOT NULL DEFAULT 'pending_payment',
  "contract_type" contract_type_enum DEFAULT 'initial',
  "verification_contract" verification_contract_status DEFAULT 'pending',
  "signature_user" text,
  "signature_owner" text,
  "rejection_feedback" text,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "payments" (
  "id" uuid PRIMARY KEY,
  "user_id" uuid NOT NULL,
  "contract_id" uuid NOT NULL,
  "order_id" varchar(100) UNIQUE NOT NULL,
  "amount" decimal(15,2) NOT NULL,
  "status" payment_status NOT NULL DEFAULT 'pending',
  "payment_type" varchar(50),
  "snap_token" text,
  "payment_date" timestamp,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "complaints" (
  "id" uuid PRIMARY KEY,
  "user_id" uuid NOT NULL,
  "room_id" uuid NOT NULL,
  "description" text NOT NULL,
  "complaint_feedback" text,
  "status" complaint_status NOT NULL DEFAULT 'sent_in',
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "favorites" (
  "id" uuid PRIMARY KEY,
  "user_id" uuid NOT NULL,
  "kost_id" uuid NOT NULL,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE TABLE "withdrawals" (
  "id" uuid PRIMARY KEY,
  "owner_id" uuid NOT NULL,
  "amount" decimal(15,2) NOT NULL,
  "target_bank" varchar(100) NOT NULL,
  "target_account_number" varchar(50) NOT NULL,
  "target_account_holder" varchar(255) NOT NULL,
  "proof" varchar(255),
  "status" withdrawal_status NOT NULL DEFAULT 'pending',
  "rejection_reason" text,
  "created_at" timestamp,
  "updated_at" timestamp
);

CREATE UNIQUE INDEX ON "favorites" ("user_id", "kost_id");

ALTER TABLE "kosts" ADD FOREIGN KEY ("owner_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "rooms" ADD FOREIGN KEY ("kost_id") REFERENCES "kosts" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "galleries" ADD FOREIGN KEY ("kost_id") REFERENCES "kosts" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "galleries" ADD FOREIGN KEY ("room_id") REFERENCES "rooms" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "contracts" ADD FOREIGN KEY ("owner_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "contracts" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "contracts" ADD FOREIGN KEY ("room_id") REFERENCES "rooms" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "payments" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "payments" ADD FOREIGN KEY ("contract_id") REFERENCES "contracts" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "complaints" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "complaints" ADD FOREIGN KEY ("room_id") REFERENCES "rooms" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "favorites" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "favorites" ADD FOREIGN KEY ("kost_id") REFERENCES "kosts" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "withdrawals" ADD FOREIGN KEY ("owner_id") REFERENCES "users" ("id") DEFERRABLE INITIALLY IMMEDIATE;
