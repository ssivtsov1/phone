DROP FUNCTION IF EXISTS rate_person;
DELIMITER //

CREATE FUNCTION rate_person (field varchar(255))
RETURNS INT                             
BEGIN
DECLARE p INT(1);
SET p = 19;


IF INSTR(field,'Директор')>0 THEN
SET p = 3;
END IF;
IF INSTR(field,'Директор')>5 THEN
SET p = 4;
END IF;
IF INSTR(field,'Генеральний директор')>0 THEN
	SET p = 1;
END IF;
IF INSTR(field,'генерального директора')>0 THEN
	SET p = 2;
END IF;
IF INSTR(field,'Начальник')>0 THEN
	SET p = 5;
END IF;
IF INSTR(field,'Начальник')>5 THEN
	SET p = 6;
END IF;
IF INSTR(field,'інженер')>0 THEN
	SET p = 9;
END IF;
IF INSTR(field,'Головний інженер')>0 THEN
	SET p = 7;
END IF;
IF INSTR(field,'Провідний')>0 THEN
	SET p = 10;
END IF;
IF INSTR(field,'Провідний інженер')>0 THEN
	SET p = 8;
END IF;


IF INSTR(field,'Керівник')>0 THEN
	SET p = 11;
END IF;
IF INSTR(field,'Адміністратор')>0 THEN
	SET p = 11;
END IF;
IF INSTR(field,'Старший')>0 THEN
	SET p = 11;
END IF;

IF INSTR(field,'Економіст')>0 THEN
	SET p = 12;
END IF;
IF INSTR(field,'Завідувач')>0 THEN
	SET p = 12;
END IF;
IF INSTR(field,'Секретар')>0 THEN
	SET p = 12;
END IF;

IF INSTR(field,'Прес-секретар')>0 THEN
	SET p = 7;
END IF;
IF INSTR(field,'Програміст')>0 THEN
	SET p = 13;
END IF;
IF INSTR(field,'Архіваріус')>0 THEN
	SET p = 13;
END IF;
IF INSTR(field,'Бухгалтер')>0 THEN
	SET p = 13;
END IF;
IF INSTR(field,'Головний бухгалтер')>0 THEN
	SET p = 3;
END IF;
IF INSTR(field,'Діловод')>0 THEN
	SET p = 13;
END IF;
IF INSTR(field,'Інспектор')>0 THEN
	SET p = 13;
END IF;
IF INSTR(field,'Майстер')>0 THEN
	SET p = 14;
END IF;
IF INSTR(field,'Електромонтер')>0 THEN
	SET p = 16;
END IF;

IF INSTR(field,'Диспетчер')>0 THEN
	SET p = 15;
END IF;
IF INSTR(field,'Технік')>0 THEN
	SET p = 17;
END IF;
IF INSTR(field,'Контролер')>0 THEN
	SET p = 18;
END IF;



IF INSTR(field,'Охоронник')>0 THEN
	SET p = 20;
END IF;
IF INSTR(field,'Водій')>0 THEN
	SET p = 21;
END IF;
IF INSTR(field,'Тракторист')>0 THEN
	SET p = 22;
END IF;
IF INSTR(field,'Прибиральник')>0 THEN
	SET p = 23;
END IF;
IF INSTR(field,'Сторож')>0 THEN
	SET p = 24;
END IF;
IF INSTR(field,'Юристконсульт')>0 THEN
	SET p = 12;
END IF;


 
RETURN p;

END//
DELIMITER ;
