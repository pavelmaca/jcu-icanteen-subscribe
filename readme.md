Úvod
====

Úèelem projektu se vytvoøit emailové notifikace na novì vystavený jídelní lístek v menze jihoèeské Univerzity.

Uživatel má možnost s pøihlásit k odbìru, který funguje na principu newsletteru. Na stránkách aplikace pouze vyplní svùj email. Pøi dalším vystavení jídelníèku ho bude aplikace informovat o vybraných specialitách.

Mnohokrát se stává, že si chce nìkdo objednat specialitu pøes internetové stránky menzi, ale ta je již vyprodaná. Díky vèasnému upozornìní lze vytvoøit objednávku vždy v èas.

Metodika
========

Jako programovací jazyk bylo zvoleno PHP. Kód mùže bìžet jak na Windows, tak OS Linux a dalších, pro které existuje zkompilované PHP a pøípadnì nìjaký http server.

Popis skriptu
=============

Bìžící aplikace je k dispozici na adrese https://menza-jcu.assassik.cz Kontrola jídelníèku probíhá v hodinových intervalech.

Samotná kontrola jídelníèku se spouští na serveru pomocí cronu. Zde se používá pøímé provádìní php scriptu pomoc pøíkazu „php“. Script naète aktuálnì zveøejnìný jídelníèek, zpracuje HTML kód ve kterém se nachází data a názvy jídel.

Datum posledního jídla je uloženo do pamìti scriptu zvaná „cache“, podle které se rozpoznávají nová jídla pøi dalším spuštìní.

V okamžiku, kdy se uživatel pøihlásí je jeho email zapsán do SQLite databáze. Pøi nalezení nového jídla script automaticky pomocí SMTP protokolu rozesílá jednotlivé upozornìní na všechny emaily, která se nacházejí v databázi.

Pokud si uživatel nepøeje dostávat další upozornìní, nachází se v emailu odkaz pro odhlášení z odbìru.

Jelikož je jídelníèek zveøejnován tøetí stranou, nelze zajistit správný chod aplikace po delší dobu, pokud tøetí strana nìjak zmìní datovou strukturu jídelníèku.

Zdrojový kód
============

Zdrojová kód je k dispozici na adrese https://github.com/pavelmaca/jcu-icanteen-subscribe

Vybraná èást kódu:

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

Závìr
=====

Aplikace momentálnì podporuje pouze upozornìní na speciality z jedné stravovny. Do budoucna by se mohla rozšíøit o další funkce, jako výbìr typù jídel a další provozovny.