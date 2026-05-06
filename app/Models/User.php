<?php

namespace App\Models;

use App\Services\ApplicantCodeService;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'applicant_code',
        'pending_deletion_at',
        'deletion_requested_by_applicant_at',
        'deletion_request_received_by_admin_at',
        'deletion_due_at',
        'deletion_warning_sent_at',
        'deletion_requested_by_admin_id',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'sex',
        'email',
        'password',
        'email_verified_at',
        'avatar_path',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'pending_deletion_at' => 'datetime',
            'deletion_requested_by_applicant_at' => 'datetime',
            'deletion_request_received_by_admin_at' => 'datetime',
            'deletion_due_at' => 'datetime',
            'deletion_warning_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            app(ApplicantCodeService::class)->assignIfMissing($user);
        });
    }

    /**
     * Defines a one-to-one relationship with a User and a Personal Information record.
     *
     * @return HasOne<PersonalInformation, User>
     */
    public function personalInformation() {
        return $this->hasOne(PersonalInformation::class, 'user_id');
    }

    /**
     * Defines a one-to-one relationship with a User and a Family Background record.
     *
     * @return HasOne<FamilyBackground, User>
     */
    public function familyBackground() {
        return $this->hasOne(FamilyBackground::class, 'user_id');
    }

    /**
     * Defines a one-to-one relationship with a User and a Educational Background record.
     *
     * @return HasOne<EducationalBackground, User>
     */
    public function educationalBackground() {
        return $this->hasOne(EducationalBackground::class, 'user_id');
    }

    /**
     * Defines a one-to-many relationship with a User and a Work Experience record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<WorkExperience, User>
     */
    public function workExperience() {
        return $this->hasMany(WorkExperience::class, 'user_id');
    }

    /**
     * Defines a one-to-many relationship with a User and a Civil Service Eligibility record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CivilServiceEligibility, User>
     */
    public function civilServiceEligibility() {
        return $this->hasMany(CivilServiceEligibility::class, 'user_id');
    }

    /**
     * Defines a one-to-many relationship with a User and a Learning and Development record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CivilServiceEligibility, User>
     */
    public function learningAndDevelopment() {
        return $this->hasMany(LearningAndDevelopment::class, 'user_id');
    }

    /**
     * Defines a one-to-many relationship with a User and a Voluntary Work record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<VoluntaryWork, User>
     */
    public function voluntaryWork() {
        return $this->hasMany(VoluntaryWork::class, 'user_id');
    }

    /**
     * Defines a one-to-many relationship with a User and a Other Information record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<VoluntaryWork, User>
     */
    public function otherInformation() {
        return $this->hasMany(OtherInformation::class, 'user_id');
    }

    /**
     * Defines a one-to-one relationship with a User and a Related Questions record.
     *
     * @return HasOne<RelatedQuestions, User>
     */
    public function relatedQuestions() {
        return $this->hasOne(RelatedQuestions::class, 'user_id');
    }

    /**
     * Defines a one-to-one relationship with a User and a Miscellaneous Information record.
     * @return HasOne<MiscInfos, User>
     */
    public function miscInfos() {
        return $this->hasOne(MiscInfos::class, 'user_id');
    }

    /**
     * User's extended profile record.
     *
     * @return HasOne<Profile, User>
     */
    public function profile() {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function documentGalleryItems(): HasMany
    {
        return $this->hasMany(DocumentGalleryItem::class, 'user_id');
    }

    /**
     * Applicant application records.
     *
     * @return HasMany<Applications, User>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Applications::class, 'user_id');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable');
    }

    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function isPendingDeletion(): bool
    {
        return !is_null($this->deletion_due_at);
    }
}
