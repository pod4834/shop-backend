public function up(): void
{
    Schema::table('scenarios', function (Blueprint $table) {
        // memo 컬럼이 없을 때만 텍스트 타입으로 추가하도록 안전하게 설정
        if (!Schema::hasColumn('scenarios', 'memo')) {
            $table->text('memo')->nullable()->after('id');
        }
    });
}