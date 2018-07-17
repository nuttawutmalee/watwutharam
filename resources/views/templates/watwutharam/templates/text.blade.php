<?php
$template_item = [
  'template' => 'about',
  'title' => 'หัวข้อย่อย',
  'date' => '2 มิ.ย. 2560',
  'content' => '<p>คลับเอ็กซ์เพรสชนะเลิศ โอ้ยไฮเปอร์แอคทีฟโซนรีโมต มะกันซิงเท็กซ์เคลื่อนย้าย เซ็นทรัล แพนดาแอ็คชั่นดัมพ์
  แรงใจซีเรียส ดีไซน์เนอร์ ศิลปวัฒนธรรมสังโฆ ออร์แกนิกหลวงพี่ ออทิสติก พรีเมียมรุสโซ เสกสรรค์ ซามูไรอุปสงค์
  สปอร์ตไลน์โอเปอเรเตอร์ อุด้งบุญคุณอุด้งสต๊อก เทียมทานแอนด์โบตั๋นสเตเดียม เห็นด้วยหมั่นโถวเพรียวบางแพนด้า
  ว้อดก้าเลสเบี้ยนฟินิกซ์แหม็บแพตเทิร์น
</p>

<img src="templates/watwutaram/assets/images/text/mock.jpg" alt="">
<p>เก๊ะวาซาบิแรลลีโดมิโนหน่อมแน้ม เวิร์ลด์ไลฟ์ คาร์โก้ ออกแบบฮาลาลเสือโคร่งสจ๊วตละอ่อน ไคลแม็กซ์ โคโยตีสปอต
  ต้าอ่วย ซามูไรแหม็บแมนชั่น น้องใหม่ แพกเกจเพทนาการจิตเภทไบเบิลเอ็กซ์เพรส ซาดิสต์ฟลุตแฟกซ์ แคร์ดีมานด์เกจิ
  อมาตยาธิปไตย เนอะเป่ายิ้งฉุบ สันทนาการแพกเกจ ภูมิทัศน์เบิร์น สเตชันแอลมอนด์ เพียบแปร้ฮองเฮาเทียมทาน
  วานิลา โฟล์คแอโรบิค</p>

<p>ชีสมัฟฟิน อินเตอร์คอนโดมิเนียมดีไซน์เนอร์ สเตชั่นเบอร์เกอร์ อริยสงฆ์แอโรบิค ไฮกุกษัตริยาธิราช ซูชิสต็อกสตูดิโอสปอร์ต
  คำสาป ทรูโปสเตอร์เมจิก แดรี่เวิร์ก อาร์พีจีทีวีเท็กซ์ สวีทสโตน สเตอริโอหมิง คาแรคเตอร์ โฮมแช่แข็งคาราโอเกะทิป
  แฮมเบอร์เกอร์ แจ็กพอตอัลตราอยุติธรรมเทป พิซซ่าคาแรคเตอร์เฟอร์นิเจอร์ เดอะออโต้เช็กบ๊อกซ์</p>

<ul>
  <li>แอปพริคอทเอ๋ผลไม้โอเพ่นฮัม สมิติเวช</li>
  <li>คลับแฟลชมั้ยเวิร์ก คณาญาติแคมเปญ</li>
  <li>บุญคุณบุญคุณแคร์ บร็อกโคลี</li>
  <li>ซีนีเพล็กซ์ไฮเทค บาบูนพาร์ตเนอร์โอเลี้ยงเมเปิล</li>
  <li>แอปพริคอทเอ๋ผลไม้โอเพ่นฮัม สมิติเวช</li>
  <li>ซีนีเพล็กซ์ไฮเทค แคมเปญบาบูนพาร์ตเนอร์ โอเลี้ยงเมเปิล</li>
  <li>คลับแฟลชมั้ยเวิร์ก คณาญาติบุญบร็อกโคลี</li>
</ul>',
'link_title' => 'ลิ้ง',
'link_url' => '#',
'link_target' => ''
]
?>

@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    ?>

@endhas

    <section class="section__text">
        <div class="section__outer">
            <div class="text__wrapper bg--body--3">
                <div class="section__content">
                    <div class="title">
                        <h2>{{ $template_item['title'] }}</h2>
                    </div>
                    <div class="date">{{ $template_item['date'] }}</div>
                    <div class="entry__content">
                        {!! $template_item['content'] !!}
                    </div>

                    <div class="text__link">
                        <a href="{{ $template_item['link_url'] }}" title="{{ $template_item['link_title'] }}" target="{{ $template_item['link_target'] }}"
                            class="btn--readmore">{{ $template_item['link_title'] }}
                            <i></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="text__bottom">
                <div class="text__bottom__inner">
                    <div class="pager">
                        <a href="text" class="btn--readmore arrow--left">ข่าวก่อนนี้
                            <i></i>
                        </a>
                        <a href="text" class="btn--readmore">ข่าวถัดไป
                            <i></i>
                        </a>
                    </div>
                    <div class="button__back">
                        <a href="news" class="btn--readmore arrow--left">กลับไปหน้าเดิม
                            <i></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>