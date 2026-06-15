<?php

/**
 * Ném ra từ tầng Repository khi vi phạm ràng buộc UNIQUE (mã lỗi MySQL 1062).
 *
 * Đây là cách "dịch" lỗi kỹ thuật của CSDL thành tín hiệu nghiệp vụ, để tầng
 * Service hiển thị thông báo thân thiện ("đã tồn tại") thay vì lộ SQLSTATE thô
 * ra người dùng (Week 6-4, Week 7-3).
 */
class DuplicateRecordException extends RuntimeException
{
}
