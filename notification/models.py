from datetime import timedelta

from django.conf import settings as django_settings
from django.contrib.auth.models import User
from django.core.exceptions import ValidationError
from django.core.validators import MaxValueValidator, MinValueValidator
from django.db import models
from django.utils import timezone

from pyclist.models import BaseModel
from true_coders.models import Coder


class Notification(BaseModel):

    EVENT = 'event'
    HOUR = 'hour'
    DAY = 'day'
    WEEK = 'week'
    MONTH = 'month'

    PERIOD_CHOICES = (
        (EVENT, 'Event'),
        (HOUR, 'Hour'),
        (DAY, 'Day'),
        (WEEK, 'Week'),
        (MONTH, 'Month'),
    )

    DELTAS = {
        EVENT: timedelta(minutes=1),
        HOUR: timedelta(hours=1),
        DAY: timedelta(days=1),
        WEEK: timedelta(weeks=1),
        MONTH: timedelta(days=31),
    }

    coder = models.ForeignKey(Coder, on_delete=models.CASCADE)
    method = models.CharField(max_length=256, null=False)
    before = models.IntegerField(null=False, validators=[MinValueValidator(0), MaxValueValidator(1000000)])
    period = models.CharField(max_length=16, choices=PERIOD_CHOICES, null=False)
    with_updates = models.BooleanField(default=True)
    with_results = models.BooleanField(default=False)
    last_time = models.DateTimeField(null=True, blank=True)
    secret = models.CharField(max_length=50, blank=True, null=True)

    def __str__(self):
        return '{0.method}@{0.coder}: {0.before} {0.period}'.format(self)

    def save(self, *args, **kwargs):
        if not self.id:
            self.last_time = timezone.now()
        if not self.secret:
            self.secret = User.objects.make_random_password(length=50)
        super().save(*args, **kwargs)

    def get_delta(self):
        return Notification.DELTAS[self.period]

    def clean(self):
        if (
            self.method == django_settings.NOTIFICATION_CONF.WEBBROWSER
            and self.period != Notification.EVENT
        ):
            raise ValidationError('WebBrowser method must have Event period.')


class Task(BaseModel):
    notification = models.ForeignKey(Notification, on_delete=models.CASCADE)
    subject = models.CharField(max_length=4096, null=True, blank=True)
    message = models.TextField(null=True, blank=True)
    addition = models.JSONField(default=dict, blank=True)
    is_sent = models.BooleanField(default=False)

    class UnsentManager(models.Manager):
        def get_queryset(self):
            return super(Task.UnsentManager, self).get_queryset().filter(is_sent=False)

    objects = models.Manager()
    unsent = UnsentManager()

    def __str__(self):
        return '{0.notification}'.format(self)
