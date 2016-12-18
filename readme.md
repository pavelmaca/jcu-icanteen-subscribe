�vod
====

��elem projektu se vytvo�it emailov� notifikace na nov� vystaven� j�deln� l�stek v menze jiho�esk� Univerzity.

U�ivatel m� mo�nost s p�ihl�sit k odb�ru, kter� funguje na principu newsletteru. Na str�nk�ch aplikace pouze vypln� sv�j email. P�i dal��m vystaven� j�deln��ku ho bude aplikace informovat o vybran�ch specialit�ch.

Mnohokr�t se st�v�, �e si chce n�kdo objednat specialitu p�es internetov� str�nky menzi, ale ta je ji� vyprodan�. D�ky v�asn�mu upozorn�n� lze vytvo�it objedn�vku v�dy v �as.

Metodika
========

Jako programovac� jazyk bylo zvoleno PHP. K�d m��e b�et jak na Windows, tak OS Linux a dal��ch, pro kter� existuje zkompilovan� PHP a p��padn� n�jak� http server.

Popis skriptu
=============

B��c� aplikace je k dispozici na adrese https://menza-jcu.assassik.cz Kontrola j�deln��ku prob�h� v hodinov�ch intervalech.

Samotn� kontrola j�deln��ku se spou�t� na serveru pomoc� cronu. Zde se pou��v� p��m� prov�d�n� php scriptu pomoc p��kazu �php�. Script na�te aktu�ln� zve�ejn�n� j�deln��ek, zpracuje HTML k�d ve kter�m se nach�z� data a n�zvy j�del.

Datum posledn�ho j�dla je ulo�eno do pam�ti scriptu zvan� �cache�, podle kter� se rozpozn�vaj� nov� j�dla p�i dal��m spu�t�n�.

V okam�iku, kdy se u�ivatel p�ihl�s� je jeho email zaps�n do SQLite datab�ze. P�i nalezen� nov�ho j�dla script automaticky pomoc� SMTP protokolu rozes�l� jednotliv� upozorn�n� na v�echny emaily, kter� se nach�zej� v datab�zi.

Pokud si u�ivatel nep�eje dost�vat dal�� upozorn�n�, nach�z� se v emailu odkaz pro odhl�en� z odb�ru.

Jeliko� je j�deln��ek zve�ejnov�n t�et� stranou, nelze zajistit spr�vn� chod aplikace po del�� dobu, pokud t�et� strana n�jak zm�n� datovou strukturu j�deln��ku.

Zdrojov� k�d
============

Zdrojov� k�d je k dispozici na adrese https://github.com/pavelmaca/jcu-icanteen-subscribe

Vybran� ��st k�du:

    $str = $td->textContent
    switch ($colCount) {
      case 0:
        // fisr col contains date
        if (preg_match('~^([0-9]{1,2}\.){2}[0-9]{4}$~', $str)) {
          $date = DateTime::createFromFormat('j.n.Y H:i:s', $str . ' 00:00:00');
        }
        break;
      case 1:
        // type of meal
        $str = self::fixEncoding($str);
        if (preg_match('~^(Specialita) [0-9]~', $str)) {
          $type = $str;
        } else {
          $skip = true;
        }
        break;
      case 3:
        // name of a meal
        $name = $str;
        break;
      }
      $colCount++;
    }

Z�v�r
=====

Aplikace moment�ln� podporuje pouze upozorn�n� na speciality z jedn� stravovny. Do budoucna by se mohla roz���it o dal�� funkce, jako v�b�r typ� j�del a dal�� provozovny.