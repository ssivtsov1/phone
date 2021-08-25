select min(a.id_name) as id,b.id,b.nazv,a.main_unit 
from vw_phone a 
left join spr_res b on trim(a.main_unit) = trim(b.nazv)
group by b.id,b.nazv,a.main_unit

CREATE FUNCTION rate_person (field varchar(255))
RETURNS INT                             
BEGIN
    DECLARE p INT;   
	IF INSTR(field,'Начальник')>0 THEN
    	SET p = 5;
	END IF;
	IF INSTR(field,'Директор')>0 THEN
    	SET p = 3;
	END IF;
	IF INSTR(field,'Генеральний директор')>0 THEN
    	SET p = 1;
	END IF;
    RETURN p;
END

SELECT *,rate_person(post) as sort FROM `vw_phone` order by sort,id_name
