# Generated by Django 2.2.10 on 2020-04-29 21:24

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('true_coders', '0028_coder_addition_fields'),
    ]

    operations = [
        migrations.AlterField(
            model_name='coder',
            name='username',
            field=models.CharField(blank=True, max_length=255, unique=True),
        ),
    ]