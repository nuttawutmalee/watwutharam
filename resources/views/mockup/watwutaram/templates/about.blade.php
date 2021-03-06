<?php
$template_item = [
  'template' => 'about',
  'content' => '<h2>ประวัติความเป็นมา</h2>
  <p>วัดวุฒาราม ตั้งแต่ พ.ศ. 2475 โดยใช่ชื่อของผู้สร้างมาตั้งเป็นชื่อของวัด ที่ดินตั้งวัดมีเนื้อที่ 22 ไร่
      1 งาน 53 4/10 ตารางวา โฉนดที่ดิน เลขที่ 16011 ได้รับพระราชทานวิสุงคามสีมา เมื่อวันที่ 31 มกราคม พ.ศ.
      2505 เขตวิสุงคามสีมากว้าง 40 เมตร ยาว 80 เมตร ปูชนียวัตถุสำคัญ คือ พระประธานในอุโบสถ และพระพุทธรูปใหญ่
      สร้างเมื่อ พ.ศ. 2531</p>
  <img src="templates/watwutaram/assets/images/about/wat.jpg" alt="">
  <h2>การบริหารการปกครอง</h2>
  <p>วัดวุฒาราม มีเจ้าอาวาสเท่าที่ทราบนาม คือรูปที่ 1 พระสิงห์ รูปที่ 2 พระคำดี รูปที่ 3 พระกุ รูปที่ 4 พระครูพิทักษ์สารเขต
      ตั้งแต่ พ.ศ. 2491 ถึงปัจจุบัน มีพระครูอรรถสารเมธีเป็นเจ้าอาวาส</p>

  <h2>การบริหารการปกครอง</h2>
  <ol>
      <li>พระครูอรรถสารเมธี เจ้าอาวาสวัดวุฒาราม (เจ้าคณะอำเภอเมืองจังหวัดขอนแก่น)</li>
      <li>พระครูสิริสารวุฒิคุณรองอาวาสวัดวุฒาราม (เจ้าคณะตำบลบึงเนียม)</li>
      <li>พระอาจารย์สม อินทคุตโต ผู้ช่วยเจ้าอาวาส</li>
      <li>พระครูอนุกูลธรรมวุฒิ ผู้ช่วยเจ้าอาวาส</li>
      <li>พระครูวุฒิธรรมาภิราม ผู้ช่วยเจ้าอาวาส</li>
      <li>พระอัจฉริยะ อรุโณ</li>
      <li>พระธวัชชัย ชยธมโม</li>
      <li>พระธีรยุทธ อานนทมนี</li>
      <li>พระมหาศรายุทธ กิตตธมมภาณี</li>
      <li>พระรัฐพล อาภาธโร</li>
      <li>พระมหาสรายุทธ กลยาณเมธี</li>
      <li>พระอำพัน จรณธมโน</li>
      <li>พระสหรัถ ยโสธโร</li>
      <li>พระธนวัฒน์ อาภสสโร</li>
  </ol>
  <p>ปัจจุบันมีประภิกษุ 14 รูปและสามเณร 5 รูป</p>

  <h2>โครงการและกิจกรรมสำคัญ</h2>
  <ol>
      <li>กิจกรรมสวดมนต์ทำวัตรเย็นทุกวันพระ เริ่มดำเนินกิจกรรมตั้งแต่ปี พ.ศ. 2552 จนถึงปัจจุบัน</li>
      <li>กิจกรรมสวดมนต์พิชิตโรคป้องกันภัย จัดทุกวันอาทิตย์โดนเริ่มดำแนินกิจกรรมตั้งแต่ปี พ.ศ. 2552 จนถึงปัจจุปัน</li>
      <li>กิจกรรมสวดสรภัญญะหมู่ โดยคณธญาติธรรมวัดวุฒาราม (ชุมชนสามัคคี)</li>
      <li>โครงการผลิตสมุนไพรบำบัดรักษาโรค</li>
      <li>โครงการผลิตและจัดทำของที่ระลึกสินค้าพื้นบ้าน</li>
      <li>กิจกรรมและโครงการสถานที่แหล่งเรียนรู้ชุมชน ภายในพิพิธภัณฑ์พื้นบ้านและศิลปะอีสาน</li>
      <li>โครงการวัดส่งเสริมสุขภาพวิถีพุทธ วัดวุฒาราม อยู่ในระดับดีมาก ในเขตจังหวัดขอนแก่น</li>
  </ol>'
]
?>

    <section class="section__about">
        <div class="section__outer">
            <div class="about__wrapper bg--body--3">
                <div class="section__content">
                    <div class="entry__content">
                        {!! $template_item['content'] !!}
                    </div>
                </div>
            </div>
        </div>
    </section>