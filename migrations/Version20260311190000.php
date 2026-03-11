<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Harden school constraints: required foreign keys, useful indexes/uniques and score check';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE sg FROM school_grade sg LEFT JOIN school_student ss ON ss.id = sg.student_id WHERE sg.student_id IS NULL OR ss.id IS NULL');
        $this->addSql('DELETE sg FROM school_grade sg LEFT JOIN school_exam se ON se.id = sg.exam_id WHERE sg.exam_id IS NULL OR se.id IS NULL');
        $this->addSql('DELETE se FROM school_exam se LEFT JOIN school_class sc ON sc.id = se.class_id WHERE se.class_id IS NULL OR sc.id IS NULL');
        $this->addSql('DELETE se FROM school_exam se LEFT JOIN school_teacher st ON st.id = se.teacher_id WHERE se.teacher_id IS NULL OR st.id IS NULL');
        $this->addSql('DELETE ss FROM school_student ss LEFT JOIN school_class sc ON sc.id = ss.class_id WHERE ss.class_id IS NULL OR sc.id IS NULL');
        $this->addSql('DELETE sc FROM school_class sc LEFT JOIN school s ON s.id = sc.school_id WHERE sc.school_id IS NULL OR s.id IS NULL');

        $this->addSql('DELETE sg1 FROM school_grade sg1 INNER JOIN school_grade sg2 ON sg1.exam_id = sg2.exam_id AND sg1.student_id = sg2.student_id AND sg1.id > sg2.id');

        $this->addSql("UPDATE school_grade SET score = 0 WHERE score < 0");
        $this->addSql("UPDATE school_grade SET score = 20 WHERE score > 20");

        $this->addSql('ALTER TABLE school_class ADD CONSTRAINT FK_5B9C53E4A6F9BFC FOREIGN KEY (school_id) REFERENCES school (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_student ADD CONSTRAINT FK_DF77A88B8A98A09F FOREIGN KEY (class_id) REFERENCES school_class (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_exam ADD CONSTRAINT FK_39C8D6F38A98A09F FOREIGN KEY (class_id) REFERENCES school_class (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_exam ADD CONSTRAINT FK_39C8D6F341807C73 FOREIGN KEY (teacher_id) REFERENCES school_teacher (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE school_grade ADD CONSTRAINT FK_DCA4BFD4CB944F1A FOREIGN KEY (student_id) REFERENCES school_student (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_grade ADD CONSTRAINT FK_DCA4BFD4A5D7E69F FOREIGN KEY (exam_id) REFERENCES school_exam (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE school_class MODIFY school_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE school_student MODIFY class_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE school_exam MODIFY class_id BINARY(16) NOT NULL, MODIFY teacher_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE school_grade MODIFY student_id BINARY(16) NOT NULL, MODIFY exam_id BINARY(16) NOT NULL');

        $this->addSql('CREATE INDEX idx_school_class_school_id ON school_class (school_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_school_class_school_name ON school_class (school_id, name)');
        $this->addSql('CREATE INDEX idx_school_student_class_id ON school_student (class_id)');
        $this->addSql('CREATE INDEX idx_school_exam_class_id ON school_exam (class_id)');
        $this->addSql('CREATE INDEX idx_school_exam_teacher_id ON school_exam (teacher_id)');
        $this->addSql('CREATE INDEX idx_school_grade_student_id ON school_grade (student_id)');
        $this->addSql('CREATE INDEX idx_school_grade_exam_id ON school_grade (exam_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_school_grade_exam_student ON school_grade (exam_id, student_id)');

        $this->addSql('ALTER TABLE school_grade ADD CONSTRAINT chk_school_grade_score_range CHECK (score >= 0 AND score <= 20)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE school_grade DROP CHECK chk_school_grade_score_range');

        $this->addSql('DROP INDEX uniq_school_grade_exam_student ON school_grade');
        $this->addSql('DROP INDEX idx_school_grade_exam_id ON school_grade');
        $this->addSql('DROP INDEX idx_school_grade_student_id ON school_grade');
        $this->addSql('DROP INDEX idx_school_exam_teacher_id ON school_exam');
        $this->addSql('DROP INDEX idx_school_exam_class_id ON school_exam');
        $this->addSql('DROP INDEX idx_school_student_class_id ON school_student');
        $this->addSql('DROP INDEX uniq_school_class_school_name ON school_class');
        $this->addSql('DROP INDEX idx_school_class_school_id ON school_class');

        $this->addSql('ALTER TABLE school_grade MODIFY student_id BINARY(16) DEFAULT NULL, MODIFY exam_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE school_exam MODIFY class_id BINARY(16) DEFAULT NULL, MODIFY teacher_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE school_student MODIFY class_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE school_class MODIFY school_id BINARY(16) DEFAULT NULL');

        $this->addSql('ALTER TABLE school_grade DROP FOREIGN KEY FK_DCA4BFD4CB944F1A');
        $this->addSql('ALTER TABLE school_grade DROP FOREIGN KEY FK_DCA4BFD4A5D7E69F');
        $this->addSql('ALTER TABLE school_exam DROP FOREIGN KEY FK_39C8D6F341807C73');
        $this->addSql('ALTER TABLE school_exam DROP FOREIGN KEY FK_39C8D6F38A98A09F');
        $this->addSql('ALTER TABLE school_student DROP FOREIGN KEY FK_DF77A88B8A98A09F');
        $this->addSql('ALTER TABLE school_class DROP FOREIGN KEY FK_5B9C53E4A6F9BFC');
    }
}
