<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## QUO Boilerplate คืออะไร?

QUO Boilerplate คือ boilerplate ที่สร้างขึ้นโดย implement จาก Laravel PHP framework ซึ่งรวมเอาปลั๊กอิน ไลบรารี่ และสิ่งจำเป็นพื้นฐานสำหรับการเริ่มต้นสร้างเว็บไซต์ในส่วนของ Frontend Developer

#
## ทำไมถึงเป็น Laravel?

จริงๆแล้ว ไม่จำกัด framework ที่ใช้ ตราบใดที่ framework หรือภาษาเขียนโปรแกรมตัวนั้นๆ รองรับการเรนเดอร์หน้าเว็บในฝั่ง server แบบเต็มรูปแบบ (full server rendering) ซึ่งภาษาหลักๆเช่น PHP, ASP, JSP รองรับส่วนนี้อยู่แล้ว แต่สำหรับ Javascript Framework รุ่นใหม่ๆเช่น Angular, React หรือ Vue นั้น ถึงแม้จะมีการทำ server rendering (เช่น pre-rendering HTML ให้เป็น static html) เพื่อรองรับ SEO สำหรับเว็บไซต์ แต่ก็ไม่สามารถเชื่อถือได้ 100%  ดังนั้นถ้าจะทำเว็บไซต์ให้เป็น SEO Friendly  Laravel ก็เป็นตัวเลือกที่ดีเนื่องจาก implement ง่ายและหลายคนคุ้นเคย (จริงๆแล้วมี Zend และ Symfony ที่เป็นคู่แข่งแต่ Laravel เป็นตัวที่คนในทีมส่วนใหญ่คุ้นเคยมากกว่าด้วย)

#
## ไม่เคยรู้จัก Laravel มาก่อน?

ไปทำความรู้จักกับมันได้ที่ [Laravel documentation](https://laravel.com/docs) มีทุกอย่างครบถ้วน (แนะนำให้ใช้เวอร์ชั่นล่าสุด)  หรือถ้าไม่ชอบอ่าน ให้เข้าไปที่ [Laracasts](https://laracasts.com) ที่จะมีวิดีโอสอน Laravel อยู่มากมาย

#
## ลิขสิทธิ์

Laravel เป็น framework ที่เปิดให้ใช้ฟรี (open source) ภายใต้ลิขสิทธิ์แบบ [MIT license](http://opensource.org/licenses/MIT).

#
## สิ่งที่ใช้ใน Boilerplate ตัวนี้

จากเดิมที่เคยมีการใช้เครื่องมือแบบ automate task runner เช่น gulp หรือ grunt ก็จะเปลี่ยนไปใช้เครื่องมือยอดนิยมเช่น webpack ช่วยในการทำ task เช่นมัดรวม javascript หรือ compile ไฟล์ preprocessor style เช่น sass หรือ less การใช้งานไม่ยุ่งยากแต่จำเป็นต้องเข้าใจหลักการทำงานของ webpack เบื้องต้นก่อน

การเขียน javascript ก็จะเปลี่ยนไปจากเดิมเล็กน้อยคือจะหันไปใช้ Ecmascript เพิ่มมากขึ้น (แต่ syntax ส่วนใหญ่ยังสามารถเขียนด้วย javascript ได้อยู่) ตัว Ecmascript จำเป็นต้องมี babel ในการ compile เป็นไฟล์ javascript เพื่อใช้งาน แต่เนื่องจาก webpack จะรับผิดชอบส่วนนี้ให้ ทำให้ลดภาระในการพัฒนาลงไปได้อีก

[Laravel-mix](https://github.com/JeffreyWay/laravel-mix) เป็นโมดูลที่รวบการปรับแต่ง webpack ให้เข้ากับการพัฒนาด้วย Laravel โมดูลตัวนี้จะช่วยลดภาระในการปรับแต่งของ webpack ที่ยุ่งยากให้กับเรา โดยที่เราต้องทำมีเพียงแค่เพิ่มการปรับแต่งเพิ่มเล็กๆน้อยๆตามความต้องการของแต่ละโปรเจคต์ก็พอ  โมดูลตัวนี้จะถูกเพิ่มมาให้แล้วสำหรับ Laravel เวอร์ชั่นใหม่ๆ

#

## ต้อง config อะไรบ้างก่อนที่จะเริ่มใช้งาน?

ต้องมีไฟล์ .env สำหรับ laravel ก่อนที่ root directory ของโปรเจคต์ หากไม่มีให้ใช้ editor สร้างไฟล์ชื่อ .env ขึ้นมาใหม่ แล้ว copy content ทุกอย่างที่อยู่ใน .env.example ไปใส่แล้ว save

ใน .env สามารถเปลี่ยนค่า setting ได้ดังนี้ (สำหรับ frontend developer เท่านั้น)

- APP_NAME ชื่อนี้จะนำไปใช้สำหรับ webpack config และ backend ด้วย เพราะฉะนั้นควรใช้เป็นตัวเล็กทั้งหมดและแทนที่ space ด้วย - (hyphen) หรือ _ (underscore)

- CMS_FRONTEND_MODE ให้ปรับเป็น true สำหรับการทำ frontend หากปรับออพชั่นนี้แล้วจะเป็น disable การทำงานของ backend ชั่วคราว หลังจากที่ทำ frontend เสร็จก็แจ้งให้ backend ทราบเพื่อที่ว่า backend จะได้ปรับออพชั่นนี้เป็น false ก่อนเริ่มการ integration ในขั้นตอนต่อไป 

> ค่าใน env อาจะมีการเปลี่ยนแปลงตามการใช้งานของ backend เพราะฉะนั้นให้เช็คกับ backend ก่อนว่าต้องใช้ค่า setting อะไรบ้าง

และอย่าลืม save

> ในส่วนของไฟล์ webpack.mix.js แทบจะไม่ต้องทำอะไรเลย (เว้นแต่ต้องการเพิ่ม webpack config ส่วนตัวไว้ใช้งาน) เพราะจะมีการดึงค่า config ต่างๆจาก env ไปใช้งานอยู่แล้ว

ถ้าเจอค่า APP_KEY ใน .env เป็นค่าว่าง ให้เปิด cmd แล้วรัน 
```
php artisan key:generate
```

#
## เริ่มต้นทำงานกับ Boilerplate ตัวนี้

1. หลังจากที่ pull ลงมาจาก repository แล้วนำเข้ามาในไดเรคทอรี่ของโปรเจคต์ที่ต้องการจะเริ่มต้นทำงานแล้ว (สมมติชื่อว่า my-laravel) ให้เข้าไปที่โฟลเดอร์ my-laravel แล้วพิมพ์คำสั่งดังนี้

```
composer update
```

> ถ้ายังไม่เคยติดตั้ง composer มาก่อน ให้ดาวน์โหลดและติดตั้งได้ที่ [getcomposer.org](https://getcomposer.org/download/) (เลือกดาวน์โหลด Composer-Setup.exe สำหรับ Windows)

#

2. จากนั้นจะต้องติดตั้ง package สำหรับ node (เอาไว้ทำงานกับ javascript และ stylesheet)
```
npm install
```
> ถ้ายังไม่เคยติดตั้ง Node.js และ npm ให้ดาวน์โหลดและติดตั้งได้จาก [Node.js](https://nodejs.org/en/) (เลือกใช้ LTS) และ [npm](https://www.npmjs.com/get-npm?utm_source=house&utm_medium=homepage&utm_campaign=free%20orgs&utm_term=Install%20npm) npm จะใช้การติดตั้งผ่าน Node command หลังจากติดตั้ง Node.js ให้ใช้คำสั่ง 
```
npm install npm@latest -g
```

#

3. หลังจากนั้นในการทำงาน frontend developer จะใช้เพียงแค่ 2 คำสั่งคือ
```
npm run watch หรือ npm run dev
```
และ
```
npm run prod
```

> npm run dev = การรัน node command ที่จำเป็นต้องใช้เพียงครั้งเดียว หากมีการเปลี่ยนแปลงโค้ด js หรือ scss ในโปรเจคต์ จะต้องรันใหม่อีกครั้งเพื่อรวมไฟล์

> npm run watch = การรัน node command แบบคอยดักจับการเปลี่ยนแปลงของไฟล์ หากมีการเปลี่ยนแปลงไฟล์ js หรือ scss ในโปรเจคต์ webpack ก็จะทำการรวมไฟล์ให้โดยอัตโนมัติ

> npm run prod = การรัน node command ในลักษณะเพื่อการรวมไฟล์สำหรับการ deployment โดยที่ webpack จะจัดการทรัพยากรและทุกอย่างที่จำเป็นต้องใช้เอาให้ใน directory ที่ชื่อ public

ทุกคำสั่งข้างต้นสามารถยกเลิกการประมวลผลได้โดยการกด
Ctrl+C 2-3 รอบ (หรือจนกว่าจะกลับไปยัง command ของ shell/command prompt ได้)

> ไม่จำเป็นต้องติดตั้ง sass compiler เพื่อ compile scss stylesheet หรือ babel compiler เพื่อ compile ecmascript เอง  ตัว webpack ที่ถูกเซ็ตไว้จะทำหน้าที่นั้นให้อยู่แล้วทุกครั้งที่รันคำสั่งด้านบน

#

## โครงสร้างไฟล์และไดเรคทอรี่

โครงสร้างไฟล์ส่วนใหญ่จะอ้างอิงจากต้นแบบของ Laravel ยกเว้นแต่ใน boilerplate ตัวนี้จะมีการเพิ่มไดเรคทอรี่ที่ใช้ทำงานกับ frontend ขึ้นมาโดยจะชื่อว่า *'src'*  ด้านใน 'src' จะประกอบไปด้วย

- assets = เอาไว้เก็บ static resources เช่น image, fonts ฯลฯ

- js = สำหรับเก็บไฟล์ js ที่เขียนขึ้นมาเอง (ตัวที่ไม่ใช่ปลั๊กอิน)

- sass = สำหรับเก็บ sass stylesheet ด้านในเป็นโครงสร้างแบบ 7-1 (ตาม sass guideline แต่มีเพิ่มเติมเล็กน้อย)

- vendors = สำหรับเก็บปลั๊กอิน javascript ที่ไม่สามารถหาจาก npm ได้ แต่ถ้าหาจาก npm ได้ให้ใช้จาก npm เป็นหลัก

ทุกอย่างใน src นี้ ไม่จำเป็นต้องนำขึ้นไปบน host (เดี๋ยวจะกล่าวถึงต่อในภายหลัง) เนื่องจาก webpack จะจัดการ copy (หรือมัดรวม)ทุกอย่างรวมถึงไฟล์ static resource ไปไว้ในโฟลเดอร์ public ตามพาธที่ได้เซ็ตไว้ในไฟล์ webpack.mix.js

## สรุป

ไฟล์และโฟลเดอร์ที่ต้องอัพโหลดขึ้นไปบน host มีดังนี้
- app
- bootstrap
- config
- database
- public
- resources
- routes
- storage
- tests
- vendors
- ไฟล์ .env 
- ไฟล์ phpunit.xml
- ไฟล์ server.php

> !!! อย่าลืมรัน npm run prod ทุกครั้งก่อนที่จะอัพโหลดไฟล์ขึ้น host (ไม่ว่าจะเป็น backend หรือ frontend)

#

## Foundation หรือ Bootstrap ดี ?

สามารถเลือกใช้ได้อย่างได้อย่างหนึ่งก็ได้ หรือจะไม่ใช้เลยก็ได้ เพียงแค่ไป remove code ส่วนของตัวที่ไม่ได้ใช้งานออกจาก main.scss และ src/js/bootstrap.js

ถ้าใช้ foundation ให้ใช้ code ชุดต่อไปนี้
```
// ใน main.scss ส่วน Vendors

@import 'vendors/foundation-settings';
@import '../../node_modules/foundation-sites/scss/foundation';
@include foundation-global-styles;
@include foundation-xy-grid-classes;
@include foundation-button;
... (import style ที่ต้องการเพิ่มได้อีก)
```
ในไฟล์ src/sass/vendors/_foundation-settings.scss เป็นไฟล์ที่ใช้ override ค่าต่างๆที่ต้องการของโค้ดดั้งเดิมของ Foundation สามารถแก้ไขตัวแปรในไฟล์นี้ได้ตามต้องการ

และในไฟล์ bootstrap.js ให้ใช้โค้ดนี้
```
require('imports-loader?$=jquery!../../node_modules/foundation-sites/dist/js/foundation.js');
```

แต่ถ้าจะใช้ Boostrap 4 แทน ก็ให้ใช้โค้ดชุดนี้ใน main.scss
```
// ใน main.scss ส่วน Vendors

@import 'vendors/bootstrap-settings';
@import '../../node_modules/bootstrap/scss/bootstrap';
```
เช่นเดียวกับ Foundation  ตัว Bootstrap เองก็สามารถ override ตัวแปรต่างๆได้เช่นกัน โดยจะสามารถแก้ไขได้ที่ src/sass/vendors/_bootstrap-settings.scss 

และในไฟล์ bootstrap.js ให้ใช้โค้ดนี้
```
require('imports-loader?$=jquery,Tether=Tether!../../node_modules/bootstrap/dist/js/bootstrap.js');
```

> ! อย่าลืมลบ style และ js ของตัวที่ไม่ใช้ออก ไม่งั้นอาจจะตีกันได้ แล้วก็อย่าลืมลบโค้ดตัวอย่างในหน้า homepage ด้วยนะ

และด้วย settings ที่มีให้ในตอนนี้ ไม่ว่าจะใช้ตัวไหนก็จะใช้ breakpoint ร่วมกันจากไฟล์ src/sass/abstracts/_variables.scss ซึ่งตัวแปร breakpoint ที่ใช้ร่วมกันอยู่คือ

```
$bp-zero: 0;
$bp-sm: 576px;
$bp-md: 768px;
$bp-lg: 992px;
$bp-xlg: 1200px;
$bp-xxlg: 1440px;
```

โดยสรุปแล้ว ตอนนี้ setting ของทั้ง Foundation และ Bootstrap ใช้ค่า breakpoint จากชุดนี้อยู่ (เพราะใน setting ของแต่ละตัวมีการ include ไฟล์นี้ไปใช้งาน) สรุปก็คือ **หากค่าในนี้เปลี่ยน breakpoint ของ responsive grid ของทั้งคู่ก็จะเปลี่ยนตามไปด้วย** (สะดวกมาก)

หากต้องการอ่านเพิ่มเติมเกี่ยวกับการตั้งค่าของทั้ง 2 framework ให้เข้าไปที่

- [Foundation 6.4](http://foundation.zurb.com/sites/docs/sass.html)

- [Bootstrap 4](https://v4-alpha.getbootstrap.com/getting-started/options/) (ตัวนี้ไม่มีอะไรมาก ตัวแปรต่างๆอยู่ในไฟล์ setting หมดแล้ว)

#

## การอัพเดทหากมีการแก้ไข original boilerplate

ถ้าหากมีการแก้ไขตัว boilerplate และต้องการอัพเดทโค้ดในโปรเจคต์ที่เอาไปใช้แล้วก่อนหน้านี้ ไฟล์ที่ต้องอัพเดทคือ

กรณีที่อัพเดทเฉพาะไฟล์สำหรับ frontend
- src/js/bootstrap.js
- src/js/app.js
- webpack.mix.js
- package.json
- ไฟล์อื่นๆที่จำเป็นในโฟลเดอร์ src/ เช่นหากมีอัพเดท mixin ของ sass ก็ต้องเอาไปอัพเดทด้วย

หลังจากนำไฟล์อัพเดทมาวางแล้ว ให้ลบไฟล์ package-lock.json ออกแล้วรันคำสั่ง
```
npm cache verify
```
แล้วตามด้วย
```
npm install
```

และในกรณีที่มีการอัพเดทไฟล์ php ของ Laravel ก็ให้แจ้งทีมว่าต้องอัพเดทไฟล์ php ไฟล์ไหนบ้าง หากมีการอัพเดท package ใน composer.json ก็ต้องแจ้งด้วย และเมื่อนำไฟล์ php ที่อัพเดทเข้ามาแล้ว ให้รันคำสั่งต่อไปนี้เพื่อให้แน่ใจว่า Laravel พร้อมใช้งาน
```
composer update
```

#

# Enjoy coding!
